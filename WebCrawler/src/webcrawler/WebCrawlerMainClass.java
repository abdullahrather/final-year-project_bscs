package webcrawler;

import java.sql.*;
import java.util.*;

import javax.swing.*;


/**
 * <p>Title: Web Crawler</p>
 *
 * <p>Description: This application will scan the URL's and generate a list of
 * links, that is not added in Database before.</p>
 *
 * <p>Copyright: Copyright (c) 2021 by Abdullah Programing</p>
 *
 * <p>Company: AliLogics Info Tech</p>
 *
 * @author Syed Abdullah
 * @version 1.0
 */
public class WebCrawlerMainClass {
    public static dlgMain dialog;
    private static int isStoped=0;

    public WebCrawlerMainClass() {
        try {
            jbInit();
        } catch (Exception ex) {
            ex.printStackTrace();
        }
    }

    public static void main(String[] args) {
        try {
            dialog = new dlgMain();
            dialog.show();
        } catch (Exception e) {
        }
    }

    private void jbInit() throws Exception {
    }

    public static String getWebPortal () {//To access WebPortal from all Classes
        return dialog.txtPortal.getText();
    }

    public static void addItemInList(String myResult) {//To show found items in List Box
        dialog.lstResult.add(myResult);
    }

    public static void addMessage(String newMsg) {//To set messages in messages area.
        dialog.lstMessages.add(newMsg);
    }

    public static int getMaxSitesToVisit() {
        return Integer.parseInt( dialog.txtMaxSites.getText() );
    }

    public static String getDomain() {
        int curIndex = dialog.cmbBoxDomain.getSelectedIndex();//Selected Domain

        if(curIndex == 1)
            return ".com";
        else if(curIndex == 2)
            return ".org";
        else if(curIndex == 3)
            return ".pk";
        else if(curIndex == 4)
            return ".net";
        else if(curIndex == 5)
            return ".edu";

        //if (curIndex == 0)
        return "|";          //<any> selected from ComboBox
    }

    public static void setStatus(String stTxt) {
        //dialog.lblPrevStatus.setText(dialog.lblStatus.getText());
        dialog.lblStatus.setText(stTxt);
    }

    public static void setStopped(int status) {
        isStoped = status;
    }

    public static int IsStopped() {
        return isStoped;
    }

    public static void clusterNsaveInDatabase(Vector ds) {
        if(!ds.isEmpty()) {
            ////////////////////Making Clusters///////////////////////
            setStatus("Making Clusters...");
            JCA jca = new JCA(5, 1000, ds);
            jca.startAnalysis();
            Vector[] v = jca.getClusterOutput();

            //WebCrawlerMainClass.setStatus("Connecting...");
            dbConnection db = new dbConnection("localhost","webspider_db","root","");
            setStatus("Saving in Database...");
            for (int i = 0; i < v.length; i++) {
                Vector tempV = v[i];
                System.out.println("-----------Cluster" + i + "---------");
                Iterator iter = tempV.iterator();
                while (iter.hasNext()) {
                    DataSets dpTemp = (DataSets) iter.next();
                    System.out.println(dpTemp.getObjName() + "[" +
                                       (int) dpTemp.getFreqOfWord() + "," +
                                       (int) dpTemp.getFreqOfURL() + "]");

                    //Save any New webpage occured.
                    if (!db.isExist("webpages", "webURL", dpTemp.getObjURL())) {
                        //String qry = new String();
                        db.runQuery("INSERT INTO webpages(webURL, webTitle, webURLFreq, meta_content, Date_Of_Crawling) VALUES ('"+ dpTemp.getObjURL() + "','" + dpTemp.getWebTitle() + "', " +
                                (int) dpTemp.getFreqOfURL() +", '"+  dpTemp.getMetaContent() + "', CURDATE())");
                    }

                    if(!db.isExistTwoCol("keywords", "webURL", "keyword", dpTemp.getObjURL(), dpTemp.getObjName())) {
                        //If its new word then Save it, with their respective URL
                        db.runQuery(
                                "INSERT INTO keywords(keyword, webURL, freqOfWord, clusterNr) VALUES ('" +
                                dpTemp.getObjName() + "', '" + dpTemp.getObjURL() + "'," +
                                (int) dpTemp.getFreqOfWord() + "," + i + ")");
                    } else {//if the keyword+URL already exist then update its clusterNr and frequency in DB.
                        db.runQuery("UPDATE keywords SET clusterNr = "+ i +" , freqOfWord = "+(int) dpTemp.getFreqOfWord()+" WHERE keyword='"+dpTemp.getObjName()+"' AND webURL='"+dpTemp.getObjURL()+"'");
                    }
                }
            }

            //WebCrawlerMainClass.setStatus("Clossing...");
            db.closeDBconn();
            PageVisitor.dataSets.clear();
            //JOptionPane.showMessageDialog(null,"Clustered & Saved Successfully!.");
            setStatus("Clustered & Saved Successfully!");
        } else {
            JOptionPane.showMessageDialog(null,"No any Data is in Volatile memory, to be Saved.");
        }
    }

    public static Vector ExtractAllFromDB() {
        setStatus("Extracting from Database...");
        Vector dsFromDB = new Vector();
        try {
            dbConnection db = new dbConnection("localhost", "webspider_db",
                                               "root", "");
            ResultSet rs = db.getRows("SELECT keyword, freqOfWord, webURLFreq, keywords.webURL, meta_content FROM webpages INNER JOIN keywords WHERE webpages.webURL = keywords.webURL");
            while (rs.next()) {
                dsFromDB.add(new DataSets(rs.getInt("freqOfWord"),
                                          rs.getInt("webURLFreq"),
                                          rs.getString("keyword"),
                                          rs.getString("webURL"), "", rs.getString("meta_content")));
                /* System.out.println(rs.getString("keyword") +" - " + rs.getString("webURL") + "|"); */
            }
            db.closeDBconn();
        } catch(SQLException e) {
        }
        setStatus("Done!");
        return dsFromDB;
    }
}
