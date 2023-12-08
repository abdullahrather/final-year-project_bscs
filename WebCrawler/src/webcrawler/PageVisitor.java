package webcrawler;

import java.net.*;
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
public class PageVisitor extends Thread  {
    public PageVisitor() {
        try {
            jbInit();
        } catch (Exception ex) {
            //ex.printStackTrace();
            JOptionPane.showMessageDialog(null, "Error while starting Page Visitor:\n\t"+
                                          ex.getStackTrace().toString());
        }
    }

    //////////////////////// To make multiple thread, have to make multiple ResultLists in GUI.
    private final static int MAX_THREADS = 1;//in case of more then 1, will have to provide multiple ResultLists in GUI
    public static int Counter=0;
    public static final int BUFSIZE = 15000; // 15kb

// To keep a volatile record of webpages, keywords and frequency of the keyword
// we need to make some datastructures. Those should be declared static so that
// only one instance is created, in the class.
    public static Vector dataSets = new Vector();
    //public static final int VMEMSIZE = 1000;
    //public static String[] vWebPage = new String[VMEMSIZE];
    //public static String[] vKeyWords = new String[VMEMSIZE];
    //public static int[] vFrequency = new int[VMEMSIZE];
    //public static int vCounter = 0;//i.e. to count total curr number of volatile memory items.

// CrowdController enforces a maximum number of entities (here:
// threads). The object is declared static so that only one instance of PageVisitor
// is ever created.
    static CrowdController threadLimiter = new CrowdController(MAX_THREADS);

// The following global hashtable is declared static so that only one
// instance is ever created. All PageVisitor threads therefore access this
// same hashtable which acts as a database of page URLs already encountered.
    static Hashtable pageDatabase = new Hashtable(); // empty to begin with

    URL pageToFetch; // communication var between go(..) and run()

//-------------------------------------------------------------------
// Constructor
// Given a String address of a Web page, transform address to URL object
// and launch the thread for this page URL. If the URL could not be built,
// don't even bother creating the thread (silently return).
//-------------------------------------------------------------------
    public PageVisitor(String pageAddress) {
        try {
            pageToFetch = new URL(pageAddress);
            String host =  pageToFetch.getHost();

            //If protocol is not HTTP, than leave it. 
            //SMO -- if (! (pageToFetch.getProtocol().equals("http")) ) {
            // ALIT - logic of http/https corrected
            if (!(pageToFetch.getProtocol().equalsIgnoreCase("http")) && !(pageToFetch.getProtocol().equalsIgnoreCase("https")) ) {
                WebCrawlerMainClass.addMessage("Web Protocol is not HTTP or HTTPS, so skipping it...");
                return;
            }

            //IF domain doesn't match to the specified than don't search
            if(host.indexOf(WebCrawlerMainClass.getDomain()) <= 0 && !WebCrawlerMainClass.getDomain().equalsIgnoreCase("|")) { // pipe '|' symbol is representing 'Any domain'
                WebCrawlerMainClass.addMessage("This domain is not "+ WebCrawlerMainClass.getDomain()+ ", so rejecting it.");
                return;
            }

            //IF robot.txt file found at this site or crawling is not allowed then skip it.
        //SMO
        /*  
        try {
                //JOptionPane.showMessageDialog(null, "seeing for robots.txt");
                URL robotURL = new URL("http://"+host+"/robots.txt");

                HTTP http = new HTTP();
                String robotContent = http.downloadWWWPage(robotURL);

                //JOptionPane.showMessageDialog(null, robotContent);
                if(robotContent.length()>0 && robotContent.indexOf("Disallow:") != -1){
                    WebCrawlerMainClass.addMessage("    Disallowed to download contents of this web-page.");
                    //return; //If it is disallowed to download contents.
                }
            } catch (MalformedURLException badURL) {
                //JOptionPane.showMessageDialog(null,"robot exceptoin");
                //robots.txt not found, or error occured while opening it.
            }
            */
            //Lower code will only be excuted if robot.txt Allows the crawlers.

            /////////Incrementing counter to keep record of sites have been visited
            Counter++;
            //System.out.println("a-"+(Counter-1));

            setName(pageAddress); // label this thread with the page name
            start(); // start the thread at run()
        } catch (MalformedURLException badURL) {
            WebCrawlerMainClass.addMessage(pageAddress + " is bad URL.. so skipping it!");
        }
    }

//-------------------------------------------------------------------
// The body of the thread (and also the heart of this program)
// in pseudo-code:
//
//   request running permission (by obtaining a ticket)
//   load pageToFetch
//   extract hypertext links from page
//   for all found links do
//     if new link
//       record link in database (so that we avoid it next time)
//       recurse for new page (launch new thread running same algorithm)
//   release ticket
//   end (thread terminates)
//-------------------------------------------------------------------
    public void run() {

        int ticket; // thread can only run for real if it gets a ticket
        String webPage, pageTitle, pageMetaData; // an entire Web page cached in a String
        Vector pageLinks; // bag to accumulate found URLs in

        // before processing the page (and possibly spawning new threads to
        // process referenced pages), obtain a ticket (i.e. a license) to do
        // so. (getTicket() will block if max number of threads are
        // already running. When another thread releases its ticket, only then
        // will a blocked thread be able to run() for real.
        ticket = threadLimiter.getTicket();

        //Display the status msg at GUI
        WebCrawlerMainClass.setStatus("Visiting...  " + pageToFetch);

        //Fetch all records needed to scan current webpage
        webPage = loadPage(pageToFetch);
        webPage = webPage.toLowerCase();//Set contents of webPage to Lower, b/c we have to compare it with <td> or <p>
        pageLinks = extractHyperTextLinks(webPage);
        pageTitle = extractTitle(webPage);   //Returns the Data written b/w <title>...</title>
        pageMetaData = extractMetaData(webPage);

        if(pageTitle.equals(""))//If title is not defined than set ThreadName to page Title.
            pageTitle = getName();//set the link (i.e. thread name) as Title
        pageTitle = pageTitle.toUpperCase();  //To show title as upper case in itemList.

        //If the requested page is not found, then its title of page will be "404 Not Found"
        if(pageTitle.compareToIgnoreCase("404 Not Found") == 0 || webPage.equals("")) {
            WebCrawlerMainClass.addMessage("404 Not Found. --> "+getName());
            WebCrawlerMainClass.setStatus("404 Not Found!");

            //Release the Ticket, so that remaining queued WebPages can be processed.
            threadLimiter.returnTicket(ticket);
            return;
        }

        WebCrawlerMainClass.addMessage(getName() + " has " + pageLinks.size() + " links.");
        WebCrawlerMainClass.addItemInList(getName() + ":");

        //Display the status msg at GUI
        WebCrawlerMainClass.setStatus("Searching for Key Words in...  " + pageToFetch);

        ///////------To check Keywords in this WebPage
        String[] keyWords = Stemmer.StemIt(webPage.split(" "));////

        ///////------To stemm MetaData/Categories of this web page
        String[] metaData = Stemmer.StemIt(pageMetaData.split(" "));
        pageMetaData = "";
        for(int i=0;  i<metaData.length; i++) {
            if(metaData[i].compareTo("")!=0) {//if Category/MetaDAta is a valid string
                pageMetaData = pageMetaData + metaData[i] + ", ";
            }
        }
        if(pageMetaData.length()>2) {
            pageMetaData = pageMetaData.substring(0, (pageMetaData.length()-2));
        }
        //JOptionPane.showMessageDialog(null, pageMetaData);

        //IF index Visited URLs are also to visit again.
        if(WebCrawlerMainClass.dialog.chkVisitedURLs.isSelected() ) {
            pageDatabase.clear();
        }

        ///Step 4:
        ////////////////////Saving in Volatile Memory DataSet/////////////////
        for(int i=0; i<keyWords.length; i++) {
            if(keyWords[i].compareTo("")!=0) {//if keyword is a valid string
                boolean isExist = false;

                //Search the current keyword in volatile memory DS if it is found
                //then update the Word frequency.
                for (int j = 0; j < dataSets.size(); j++) {
                    if (keyWords[i].compareToIgnoreCase(((DataSets)(dataSets.get(j))).getObjName()) == 0 &&
                        getName().compareToIgnoreCase(((DataSets)(dataSets.get(j))).getObjURL()) == 0) {
                        ((DataSets)(dataSets.get(j))).incWordFreq();
                        isExist = true;
                        break;
                    }
                }  //If the current keyword is not found in volatile memory then
                //then add it into volatile memory.
                if (!isExist) {
                    dataSets.add(new DataSets(1,1,keyWords[i],getName(), pageTitle, pageMetaData));
                }
            }
        }

        ///////////////////Displaying found Keywords of only current page//////////////
        for(int i=0; i<dataSets.size();i++) {
            if(getName().compareToIgnoreCase(((DataSets)(dataSets.get(i))).getObjURL()) == 0) {
                WebCrawlerMainClass.addItemInList("   " +
                                                  ((DataSets) (dataSets.get(i))).
                                                  getObjName() + " (" +
                                                  (int) ((DataSets) (dataSets.
                        get(i))).getFreqOfWord() + ")");
            }
        }

        //Counter to fetch only # of sites, those has been defined in TxtField
        int maxSitesToSearch = Integer.parseInt(WebCrawlerMainClass.dialog.txtMaxSites.getText());

        // Now process all found URLs
        Enumeration enum1 = pageLinks.elements();
        while (enum1.hasMoreElements() && maxSitesToSearch > this.Counter) {
            //maxSitesToSearch--; //Decrementing counter
            //System.out.println(this.Counter+"");
            String page = (String)enum1.nextElement();

            //WebCrawlerMainClass.addMessage("");//To insert a line break in messages list.
            //WebCrawlerMainClass.addMessage("Visiting... " + page);

            if (!alreadyVisited(page)) {
                markAsVisited(page);

                // print hypertext link relationship to Output list box.
                WebCrawlerMainClass.addMessage("   "+ pageTitle + " --> " + page);
                //WebCrawlerMainClass.addItemInList("   "+ pageTitle + " --> " + page);

                if(WebCrawlerMainClass.IsStopped() == 1) {//If user clicked at Stop button,
                    WebCrawlerMainClass.addMessage("");//To insert a line break in Messages area.
                    //WebCrawlerMainClass.addMessage("ABNORMAL STOP!!!");
                    WebCrawlerMainClass.setStatus("ABNORMAL STOP!!!");
                    threadLimiter.releaseAllTickets();//this function will impact on all running threads
                    this.stop();//It will stop only the current thread.
                    //return;                 //than condition will true and new thread will not start.
                } else {
                    // and recursively start up a new thread to handle new page
                    new PageVisitor(page);

                    // We're done with our page, so release ticket and let another thread
                    // process a page (returning our ticket will un-block some other
                    // waiting thread.
                    threadLimiter.returnTicket(ticket);

                }
            } else {
                WebCrawlerMainClass.addMessage("   Already processed! "+page);
                //Search the current URL in volatile memory DS if it is found
                //then update the URL frequency.
                for (int j = 0; j < dataSets.size(); j++) {
                    if (page.compareToIgnoreCase(((DataSets) (dataSets.get(
                            j))).getObjURL()) == 0) {

                        ((DataSets) (dataSets.get(j))).incURLFreq();
                    }
                }
            }
        }

        WebCrawlerMainClass.setStatus("Completed.");

        // insert some brief, random delay here before running off the end of the
        // world (of run()). This randomizes the scheduling of threads waiting to
        // obtain a ticket.
        try {
            Thread.sleep((int) (Math.random() * 200));
        } catch (Exception e) {}
    }

//-------------------------------------------------------------------
// Given a valid WWW page URL, fetch and return the page as a big String.
//-------------------------------------------------------------------
    protected String loadPage(URL page) {
        HTTP http;
        http = new HTTP();
        return http.downloadWWWPage(page);
    }

//-------------------------------------------------------------------
// Given a String containing a legal HTML page, extract all
// http://.... -style strings.
// Return list of links as a Vector of Strings.
//-------------------------------------------------------------------
    protected Vector extractHyperTextLinks(String page) {

        int lastPosition = 0; // position of "http:" substring in page
        int endOfURL; // pos of end of http://........
        String link; // the link we're after
        Vector bagOfLinks = new Vector(); // a bag to accumulate interesting links

        while (lastPosition != -1) {
            lastPosition = page.indexOf("http://", lastPosition);

            if (lastPosition != -1) {
                endOfURL = page.indexOf(">", lastPosition + 1);//ends with > .

                // extract found hypertext link
                link = page.substring(lastPosition, endOfURL);
                link = link.trim();
                if (link.indexOf("\"") > 0)
                    link = link.substring(0, link.indexOf("\""));
                if (link.indexOf("\'") > 0)
                    link = link.substring(0, link.indexOf("\'"));
                if (link.endsWith("\""))
                    link = link.substring(0, link.length() - 1);

                // ignore refereces to the same page
                if (link.indexOf("#") != -1) {
                    link = link.substring(0, link.indexOf("#"));
                }

                // discard links which point explicitly to images
                if (link.endsWith(".gif") ||
                    link.endsWith(".jpg") ||
                    link.endsWith(".swf") ||
                    link.endsWith(".jpeg") ||
                    link.endsWith(".bmp")) {
                    ;
                } else { // collect all others
                    bagOfLinks.addElement(link);
                    // System.out.println( link );
                }

                lastPosition++; // skip current link
            }
        }
        return bagOfLinks;
    }

//-------------------------------------------------------------------
// Given a String containing a legal HTML page, extract all
// <p>..</p> or <td>..</td> -style strings.
// Return a String value that is in between of these tags
//-------------------------------------------------------------------
    protected String extractContent(String page) {

        int lastP = 0, lastTD = 0, endTag; // position of "<p>" substring in page
        String content = ""; // the title we're after

        while(lastP != -1) {
            lastP = page.indexOf("<p ", lastP);

            if(lastP != -1) {
                endTag = page.indexOf(">", lastP)+1;

                int strtP = endTag, endP = page.indexOf("</p>", strtP);
                try {
                    if (strtP != -1 && endP != -1 && strtP < endP)
                        content = content +" "+ page.substring(strtP, endP);
                } catch (StringIndexOutOfBoundsException ioe) {
                }

                if(lastP!= -1)
                    lastP++;
            }
        }
        while(lastTD != -1) {
            lastTD = page.indexOf("<td ", lastTD);

            if(lastTD != -1) {
                endTag = page.indexOf(">", lastTD)+1;

                int strtTD = endTag, endTD = page.indexOf("</td>", strtTD);
                try {
                    if (strtTD != -1 && endTD != -1 && strtTD < endTD)
                        content = content +" "+ page.substring(strtTD, endTD);
                } catch (StringIndexOutOfBoundsException ioe) {
                }

                if(lastTD!= -1)
                    lastTD++;
            }
        }
        return content;
    }

//-------------------------------------------------------------------
// Given a String containing a legal HTML page, extract all
// <meta>..</meta> -style strings.
// Return a String value that is in between of these tags
//-------------------------------------------------------------------
            protected String extractMetaData(String page) {
                String metaContent = ""; // the title we're after
                int begngMeta = page.indexOf("<meta") + 5,
                        strtS = page.indexOf(">", begngMeta)+1,
                                endS = page.indexOf("</meta>", strtS);
                try {
                    if (strtS != -1 && endS != -1 && strtS < endS)
                        metaContent = page.substring(strtS, endS);;
                } catch(StringIndexOutOfBoundsException ioe) {
                }
                return metaContent;
        }

//-------------------------------------------------------------------
// Given a String containing a legal HTML page, extract all
// <title>..</title> -style strings.
// Return a String value that is in between of these tags
//-------------------------------------------------------------------
        protected String extractTitle(String page) {
            String title = ""; // the title we're after
            int strtS = page.indexOf("<title>") + 7,
                    endS = page.indexOf("</title>", strtS);
            try {
                if (strtS != -1 && endS != -1 && strtS < endS)
                    title = page.substring(strtS, endS);;
            } catch(StringIndexOutOfBoundsException ioe) {
            }
            return title;
        }

//-------------------------------------------------------------------
// Find out whether a page has already been encountered in the past.
//-------------------------------------------------------------------
    protected boolean alreadyVisited(String pageAddr) {
        return pageDatabase.containsKey(pageAddr);
    }

//-------------------------------------------------------------------
// Mark a page as "visited before". Subsequent encounters with this
// page will then skip it.
//-------------------------------------------------------------------
    protected void markAsVisited(String pageAddr) {
        pageDatabase.put(pageAddr, pageAddr); // add page to DB
    }

    private void jbInit() throws Exception {
    }
    //-------------------------------------------------------------------
} // End of Class PageVisitor
