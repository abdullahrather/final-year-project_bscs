package webcrawler;

/**
 * <p>Title: Web Crawler</p>
 *
 * <p>Description: This application will scan the URL's and generate a list of
 * links, that is not added in Database before.
 * This class represents a candidate for Cluster analysis. A candidate must have
 * a name and two independent variables on the basis of which it is to be clustered.
 * A Data Point must have two variables and a name. A Vector of  Data Point object
 * is fed into the constructor of the JCA class. JCA and DataPoint are the only
 * classes which may be available from other packages. </p>
 *
 * <p>Copyright: Copyright (c) 2021 by Abdullah Programing</p>
 *
 * <p>Company: AliLogics Info Tech</p>
 *
 * @author  Abdullah
 * @version 1.0
 */

public class DataSets {

    private double frequencyOfWord;//it will behave as X-axis in a graph
    private double frequencyOfURL;//it will behave as Y-axis in a graph
    private String mObjName;
    private String mObjURL;
    private String mObjPageTitle;
    private String mObjMetaContent;
    private Cluster mCluster;
    private double mEuDt;

    /* This is the constructor of objects DataSets, and it can be used as:
     -> freqOfWord = 0 && freqOfURL > 0 to make Clusters on the basis of freqOfWord
     -> freqOfWord > 0 && freqOfURL = 0 to make Clusters on the basis of freqOfURL
     -> freqOfWord > 0 && freqOfURL > 0 to make Clusters on the basis of both.
     */
    public DataSets(double freqOfWord, double freqOfURL, String name, String url, String title, String metaContent) {
        this.frequencyOfWord = freqOfWord;
        this.frequencyOfURL = freqOfURL;
        this.mObjName = name;
        this.mObjPageTitle = title;
        this.mObjMetaContent = metaContent;
        this.mObjURL = url;
        this.mCluster = null;
    }

    public void setCluster(Cluster cluster) {
        this.mCluster = cluster;
        calcEuclideanDistance();
    }

    public void calcEuclideanDistance() {

    //called when DP is added to a cluster or when a Centroid is recalculated.
        mEuDt = Math.sqrt(Math.pow((frequencyOfWord - mCluster.getCentroid().getCx()),
2) + Math.pow((frequencyOfURL - mCluster.getCentroid().getCy()), 2));
    }

    public double testEuclideanDistance(Centroid c) {
        return Math.sqrt(Math.pow((frequencyOfWord - c.getCx()), 2) + Math.pow((frequencyOfURL - c.getCy()), 2));
    }

    public String getWebTitle() {
        return this.mObjPageTitle;
    }


    public String getMetaContent() {
        return this.mObjMetaContent;
    }


    public double getFreqOfWord() {
        return frequencyOfWord;
    }

    public double getFreqOfURL() {
        return frequencyOfURL;
    }

    public Cluster getCluster() {
        return mCluster;
    }

    public double getCurrentEuDt() {
        return mEuDt;
    }

    public String getObjName() {
        return mObjName;
    }

    public String getObjURL() {
        return mObjURL;
    }

    public void incWordFreq() {
        this.frequencyOfWord++;
    }

    public void incURLFreq() {
        this.frequencyOfURL++;
    }
}
