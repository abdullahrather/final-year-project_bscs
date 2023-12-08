package webcrawler;

import java.io.*;
import java.net.*;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

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
//-------------------------------------------------------------------
// Class HTTP contains some high-level methods to download Web pages.
//-------------------------------------------------------------------
class HTTP {

public final static int HTTP_PORT = 80;     // well-known WWW port

//DataInputStream in;         // the enhanced textline input stream
BufferedReader br;

//-------------------------------------------------------------------
// Given a valid page URL, grab it off the Net and return it as a single
// String.
//-------------------------------------------------------------------
public String downloadWWWPage(URL pageURL) {

    String host, file;
    BufferedReader pageStream = null;

    host = pageURL.getHost();
    file = pageURL.getFile();

    System.out.println("Host to contact: '" + host +"'");
    System.out.println("File to fetch  : '" + file +"'");
    
    // ALIT START - Accept all SSL certificates to work with HTTPS
    // Create a new trust manager that trust all certificates
    TrustManager[] trustAllCerts = new TrustManager[]{
        new X509TrustManager() {
            public java.security.cert.X509Certificate[] getAcceptedIssuers() {
                return null;
            }
            public void checkClientTrusted(
                java.security.cert.X509Certificate[] certs, String authType) {
            }
            public void checkServerTrusted(
                java.security.cert.X509Certificate[] certs, String authType) {
            }
        }
    };

    // Activate the new trust manager
    try {
        SSLContext sc = SSLContext.getInstance("SSL");
        sc.init(null, trustAllCerts, new java.security.SecureRandom());
        HttpsURLConnection.setDefaultSSLSocketFactory(sc.getSocketFactory());
    } catch (Exception e) {
    }
    // ALIT STOP SSL - Accept all certificates

    try {
        pageStream = getPageStream(pageURL);//ALIT NEW METHOD for HTTPS - getWWWPageStream(host, file);
        if (pageStream == null) {
            System.out.println("BufferedReader is EMPTY");
            return "";
        } else {
            System.out.println("BufferedReader contains value");
        }
    } catch (Exception error) {
        System.out.println("get(host, file) failed!" + error);
        error.printStackTrace();
        WebCrawlerMainClass.addMessage("Error in Downloading! " + error);
        WebCrawlerMainClass.setStatus("Error Occured!");
        //WebCrawlerMainClass.addItemInList("get(host, file) failed!" + error);
        //JOptionPane.showMessageDialog(null, "get(host, file) failed!" + error);
        return "";
    }

    //DataInputStream in = new DataInputStream(pageStream);
    StringBuffer pageBuffer = new StringBuffer();
    String line;

    try {
        while ( (line = pageStream.readLine())!=null && line.length() >= 0 ) {
            System.out.println(line);
            pageBuffer.append(line);
        }
        System.out.println("\n-------------------\n");
     } catch (IOException ioe) {
         System.err.println("NOT A VALID URL (IOException): "+pageURL.getPath());
     } catch (NullPointerException npe) {
         System.err.println("NOT A VALID URL (NullPointerException): "+pageURL.getPath());
     }
    
//    try {
//        while ((line = pageStream.readLine()) != null) {
//            System.out.println(line);
//            pageBuffer.append(line);
//        }
//    } catch (Exception error) { 
//        error.printStackTrace();
//    }

    try {
        pageStream.close();
    } catch (Exception ignored) {}

    return pageBuffer.toString();
}

public BufferedReader getPageStream (URL url) throws Exception {
    
    //String search="https://paknokri.com/index.faces";
    //String path="C:\\Users\\QV\\Desktop\\CrawlerNPortal\\test.txt";
    // This will get input data from the server
    InputStream inputStream = null;

    // This user agent is for if the server wants real humans to visit
    String USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36";

    // This socket type will allow to set user_agent
    URLConnection con = url.openConnection();

    // Setting the user agent
    con.setRequestProperty("User-Agent", USER_AGENT);

    //Getting content Length
    int contentLength = con.getContentLength();
    System.out.println("File contentLength = " + contentLength + " bytes");

    // Requesting input data from server
    inputStream = con.getInputStream();

    // transform into global BufferedReader, so that it may be used by caller
    br = new BufferedReader(new InputStreamReader(inputStream)); 

    // read response until blank separator line
     String response;

    //response = br.readLine();   //Read first line, to predict the response type.
    //if(response.indexOf("HTTP/1.1 200 OK") < 0) { //if the requested page is not accessable
    //    System.out.println(response);//If not OK response
    //   return null;
    //}else
    //    System.out.println(response);//if the response is OK

//     try {
//        while ( (response = br.readLine())!=null && response.length() >= 0 ) {
//            System.out.println(response);
//        }
//        System.out.println("\n-------------------\n");
//     } catch (IOException ioe) {
//         System.err.println("NOT A VALID URL (IOException): "+url.getPath());
//     } catch (NullPointerException npe) {
//         System.err.println("NOT A VALID URL (NullPointerException): "+url.getPath());
//     }
    //inputStream.close();
    
    return br;
}

//-------------------------------------------------------------------
// Given a host and file spec, connect to the Web server and request
// the file document, read in the HTTP response and return an inputstream
// containing the requested Web document
//-------------------------------------------------------------------
public BufferedReader getWWWPageStream (String host, String file)
                            throws IOException, UnknownHostException {

Socket          httpPipe;   // the TCP socket to the Web server
InputStream     inn;        // the raw byte input stream from server
OutputStream    outt;       // the raw byte output stream to server
PrintStream     out;        // the enhanced textline output stream
InetAddress     webServer;  // the address of the Web server

    webServer = InetAddress.getByName(host);

    httpPipe = new Socket(webServer, HTTP_PORT);
    if (httpPipe == null) {
        System.out.println("Socket to Web server creation failed.");
        WebCrawlerMainClass.addMessage("Socket to Web server creation failed.");
        //WebCrawlerMainClass.addItemInList("Socket to Web server creation failed.");
        //JOptionPane.showMessageDialog(null, "Socket to Web server creation failed.");
        return null;
    }

    inn  = httpPipe.getInputStream();    // get raw streams
    outt = httpPipe.getOutputStream();

    br = new BufferedReader(new InputStreamReader(inn));    
    //in   = new DataInputStream(inn);     // turn into higher-level ones
    out  = new PrintStream(outt);

    if (inn==null || outt==null) {
        System.out.println("Failed to open streams to socket.");
        WebCrawlerMainClass.addMessage("Failed to open streams to socket.");
        //WebCrawlerMainClass.addItemInList("Failed to open streams to socket.");
        //JOptionPane.showMessageDialog(null, "Failed to open streams to socket.");
        return null;
    }

    // send GET HTTP request
    out.println("GET " + file + " HTTP/1.0\n");

    // read response until blank separator line
    String response;
    response = br.readLine();   //Read first line, to predict the response type.
    if(response.indexOf("HTTP/1.1 200 OK") < 0) { //if the requested page is not accessable
        System.out.println(response);//If not OK response
       return null;
    }else
        System.out.println(response);//if the response is OK

    while ( (response = br.readLine()).length() > 0 ) {
        System.out.println(response);
    }
    System.out.println("-------------------");

    return br;      // return InputStream to allow client to read resource
}
}// End of Class HTTP
