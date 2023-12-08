package webcrawler;



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
public class WebExplorer extends Thread {

    public void run() {
        //new WebExplorer().doIt(WebCrawlerMainClass.getWebPortal());
        // create the root instance of a PageVisitor thread. This thread
        // will load the initial page and spawn more threads for every link
        // found in that page.

        ///Initializing counter of pageVisitor
        PageVisitor.Counter = 0;

        new PageVisitor(WebCrawlerMainClass.getWebPortal());

    }
/*
    public void doIt(String args) {
        // create the root instance of a PageVisitor thread. This thread
        // will load the initial page and spawn more threads for every link
        // found in that page.

        ///Initializing counter of pageVisitor
        PageVisitor.Counter = 0;

        new PageVisitor(args);
    }*/
}
