package webcrawler;

import java.sql.*;

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
 * @author not attributable
 * @version 1.0
 */
public class dbConnection {
    private Connection con = null;
    private Statement stmt;

    ////////////////////////////////Main Constructor/////////////////////////
    public dbConnection(String serverName, String mydatabase, String username, String password) {
        try {
            //Explicitly specifying J-Connection Driver name
            Class.forName("com.mysql.jdbc.Driver").newInstance();

            // Create a url to the database
            String url = "jdbc:mysql://" + serverName + "/" + mydatabase; // a JDBC url

            //Creating connection by DriverManger class to DB
            con = DriverManager.getConnection(url, username, password);

            //Creating Statement(i.e. a vehicle for sending SQL and receiving ResultSet)
            stmt = con.createStatement();

            //con = DriverManager.getConnection("jdbc:mysql:///taximangsys", "root", "");
            //if (!con.isClosed())
            //    JOptionPane.showMessageDialog(null,"Successfully connected to MySQL server...");

        } catch (Exception e) {
            JOptionPane.showMessageDialog(null, "Cant connect to Database: " + e.getMessage() +"\nCause: "+ e.getCause());
        }
    }

    ////////////////Close the DB connection created.//////////////////////////
    public void closeDBconn() {
        try {
            if (con != null)
                con.close();
        } catch (SQLException e) {}
    }

    ///////////////Execute UPDATE, DELETE or INSERT query/////////////////////
    public int runQuery(String query) {

        //if SELECT is written in begining of query string.
        //if(query.indexOf("SELECT") >= 0) {
        //    JOptionPane.showMessageDialog(null, "SELECT query is not allowed to pass in runQuery()");
        //} else {//if UPDATE, DELETE or INSERT query is passed to function.
            try {
                return (stmt.executeUpdate(query));
            } catch(SQLException e) {
                //JOptionPane.showMessageDialog(null,"EXCEPTION: " + e);
                System.out.println("EXCEPTION: " + e +" -"+query+"- ");
            }
        //}

        return 1;
    }

    ///////////////////////////returns a ResultSet to calling//////////////
    public ResultSet getRows(String query) {
        ResultSet rs = null;

        if(query.indexOf("SELECT") >= 0) {
            try {
                rs = stmt.executeQuery(query);
                //rs = stmt.getResultSet();
                //JOptionPane.showMessageDialog(null, ""+stmt.getFetchSize()+rs.getFetchSize());
            } catch(SQLException e) {
                rs = null;
                JOptionPane.showMessageDialog(null,"EXCEPTION: " + e);
            }
        } else {
            JOptionPane.showMessageDialog(null, "Invalid Query type!");
        }

        return rs;
    }

    /////////////////If more then 1 string type values of a Col exists/////////
    public boolean isExist(String tbl, String col, String val) {
        //Select all rows matching specified value
        ResultSet rs = this.getRows("SELECT "+col+" FROM "+tbl+" WHERE "+col+"='"+val+"'");

        //if fetched rows are not found.
        try {
            if (!rs.next()) {
                return false;
            }
        }catch(SQLException e) {}

        //else if matching rows found.
        return true;
    }

    /////////////If more then 1 double type values of a Col exists/////////////
    public boolean isExist(String tbl, String col, double val) {
        //Select all rows matching specified value
        ResultSet rs = this.getRows("SELECT "+col+" FROM "+tbl+" WHERE "+col+"="+val);

        //if fetched rows are not found.
        try {
            if (!rs.next()) {
                return false;
            }
        }catch(SQLException e) {}

        //else if matching rows found.
        return true;
    }

    /////////////////If more then 1 string type values of two Cols exists/////////
    public boolean isExistTwoCol(String tbl, String col1, String col2, String val1, String val2) {
        //Select all rows matching specified value
        ResultSet rs = this.getRows("SELECT "+col1+" FROM "+tbl+" WHERE "+col1+"='"+val1+"' AND "+col2+"='"+val2+"' ");

        //if fetched rows are not found.
        try {
            if (!rs.next()) {
                return false;
            }
        }catch(SQLException e) {}

        //else if matching rows found.
        return true;
    }

}
