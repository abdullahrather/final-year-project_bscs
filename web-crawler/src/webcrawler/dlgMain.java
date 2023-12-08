package webcrawler;

import java.awt.*;
import java.awt.event.*;

import javax.swing.*;
import javax.swing.border.*;

//import com.borland.jbcl.layout.XYLayout;
//import com.borland.jbcl.layout.*;

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
public class dlgMain extends JDialog {
    //JPanel panel1 = new JPanel();
    BorderLayout borderLayout1 = new BorderLayout();
    JPanel myPanel = new JPanel();
    Border border1 = BorderFactory.createLineBorder(Color.black, 2);
    Border border2 = new TitledBorder(border1, "Web Crawler");
    JLabel jLabel1 = new JLabel();
    JTextField txtMaxSites = new JTextField();
    JLabel jLabel2 = new JLabel();
    JComboBox cmbBoxDomain = new JComboBox();
    Border border3 = BorderFactory.createLineBorder(Color.white, 2);
    Border border4 = BorderFactory.createLineBorder(Color.white, 2);
    JLabel jLabel3 = new JLabel();
    JTextField txtPortal = new JTextField();
    JButton btnStart = new JButton();
    JButton btnStop = new JButton();
    JButton btnClear = new JButton();
    JButton btnExit = new JButton();
    Border border5 = BorderFactory.createLineBorder(Color.black, 2);
    //List lstResult = new List();
    JCheckBox chkVisitedURLs = new JCheckBox();
    Border border6 = BorderFactory.createLineBorder(SystemColor.controlText, 2);
    BorderLayout borderLayout2 = new BorderLayout();
    List lstResult = new List(); //List lstResult = new List();
    List lstMessages = new List();
    JSplitPane jspContainer;
    JLabel lblStatus = new JLabel();
    JMenuBar topMenu = new JMenuBar();
    Panel btnsPanel = new Panel();
    FlowLayout flowLayout1 = new FlowLayout();
    GridBagLayout gridBagLayout1 = new GridBagLayout();
    JButton btnSave = new JButton();
    JMenuItem miRedoClust = new JMenuItem();
    JMenuItem miExit = new JMenuItem();
    JMenuItem miHelp = new JMenuItem();
    JMenuItem miAbout = new JMenuItem();

    public dlgMain(Frame owner, String title, boolean modal) {
        super(owner, title, modal);
        try {
            setDefaultCloseOperation(DISPOSE_ON_CLOSE);
            jbInit();
            pack();
        } catch (Exception exception) {
            exception.printStackTrace();
        }
    }

    public dlgMain() {
        this(new Frame(), "Abdullah Programming", false);
        this.setSize(800, 572);
        this.setResizable(true);
        // center the frame on the screen
        Dimension oursize = getSize();
        Dimension screensize = Toolkit.getDefaultToolkit().getScreenSize();
        int x = (screensize.width - oursize.width) / 2;
        int y = (screensize.height - oursize.height) / 2 - 14;
        x = Math.max(0, x); // keep the corner on the screen
        y = Math.max(0, y); //
        this.setLocation(x, y);
    }
    private void jbInit() throws Exception {
        //panel1.setLayout(borderLayout1);
        myPanel.setBackground(new Color(212, 208, 200));
        myPanel.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        myPanel.setAlignmentY((float) 0.5);
        myPanel.setBorder(border2); //myPanel.setNextFocusableComponent(null);
        myPanel.setMaximumSize(new Dimension(1044, 60));
        myPanel.setToolTipText("");
        myPanel.setActionMap(null);
        myPanel.setLayout(gridBagLayout1);
        txtMaxSites.setPreferredSize(new Dimension(75, 19));
        jLabel2.setMaximumSize(new Dimension(172, 15));
        jLabel2.setPreferredSize(new Dimension(169, 15));
        jLabel2.setText("Domains to Search:");
        cmbBoxDomain.setBackground(Color.white);
        cmbBoxDomain.setBorder(null);
        cmbBoxDomain.addItem(new String("<any>"));
        cmbBoxDomain.addItem(new String(".com"));
        cmbBoxDomain.addItem(new String(".org"));
        cmbBoxDomain.addItem(new String(".pk"));
        cmbBoxDomain.addItem(new String(".net"));
        cmbBoxDomain.addItem(new String(".edu"));
        cmbBoxDomain.setSelectedIndex(0);
        this.setDefaultCloseOperation(javax.swing.WindowConstants.
                                      DISPOSE_ON_CLOSE);
        this.setTitle("Abdullah Programing - IQRA University");
        this.addWindowListener(new dlgMain_this_windowAdapter(this));
        this.getContentPane().setLayout(borderLayout2);
        jLabel1.setMaximumSize(new Dimension(172, 15));
        jLabel1.setPreferredSize(new Dimension(169, 15));
        jLabel1.setHorizontalAlignment(SwingConstants.LEFT);
        jLabel1.setText("Maximum Sites to Visit:");
        txtMaxSites.setText("100");
        txtMaxSites.addKeyListener(new dlgMain_txtMaxSites_keyAdapter(this));
        jLabel3.setMaximumSize(new Dimension(169, 15));
        jLabel3.setPreferredSize(new Dimension(169, 15));
        jLabel3.setHorizontalAlignment(SwingConstants.LEFT);
        jLabel3.setText("Portal (starting site):");
        btnStart.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        btnStart.setMaximumSize(new Dimension(65, 25));
        btnStart.setPreferredSize(new Dimension(62, 25));
        btnStart.setText("Start");
        btnStart.addActionListener(new dlgMain_btnStart_actionAdapter(this));
        btnStop.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        btnStop.setMaximumSize(new Dimension(65, 25));
        btnStop.setPreferredSize(new Dimension(62, 25));
        btnStop.setText("Stop");
        btnStop.addActionListener(new dlgMain_btnStop_actionAdapter(this));
        btnClear.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        btnClear.setMaximumSize(new Dimension(65, 23));
        btnClear.setMinimumSize(new Dimension(55, 23));
        btnClear.setPreferredSize(new Dimension(62, 25));
        btnClear.setText("Clear");
        btnClear.addActionListener(new dlgMain_btnClear_actionAdapter(this));
        btnExit.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        btnExit.setMaximumSize(new Dimension(62, 25));
        btnExit.setPreferredSize(new Dimension(62, 25));
        btnExit.setText("Exit");
        btnExit.addActionListener(new dlgMain_btnExit_actionAdapter(this));
        chkVisitedURLs.setMaximumSize(new Dimension(500, 23));
        chkVisitedURLs.setPreferredSize(new Dimension(327, 23));
        chkVisitedURLs.setContentAreaFilled(false);
        chkVisitedURLs.setHorizontalAlignment(SwingConstants.CENTER);
        chkVisitedURLs.setText("Visit the URLs those have already visited.");
        txtPortal.setPreferredSize(new Dimension(152, 19));
        txtPortal.setText("http://");
        lstResult.setFont(new java.awt.Font("Dialog", Font.PLAIN, 10));
        lblStatus.setBorder(border1);
        lblStatus.setPreferredSize(new Dimension(117, 50));
        lblStatus.setToolTipText("");
        lblStatus.setText("Status text goes here...");
        lblStatus.setVerticalAlignment(SwingConstants.TOP);
        jspContainer = new JSplitPane(JSplitPane.VERTICAL_SPLIT, false, lstResult, lstMessages);
        jspContainer.setOneTouchExpandable(true);
        btnsPanel.setLayout(flowLayout1);
        btnSave.setPreferredSize(new Dimension(62, 25));
        btnSave.setActionCommand("Save");
        btnSave.setText("Save");
        btnSave.addActionListener(new dlgMain_btnSave_actionAdapter(this));
        miRedoClust.setText("Redo Clustering");
        miRedoClust.addActionListener(new dlgMain_miRedoClust_actionAdapter(this));
        miHelp.setText("Help");
        miAbout.setText("About");
        miExit.setText("Quit");
        miExit.addActionListener(new dlgMain_miExit_actionAdapter(this));
        this.getContentPane().add(lblStatus, java.awt.BorderLayout.SOUTH);
        this.getContentPane().add(jspContainer, java.awt.BorderLayout.CENTER);
        this.setJMenuBar(topMenu);

        JMenu file = new JMenu("File", true);
        JMenu hlp = new JMenu("Help", true);

        topMenu.add(file); //this.getContentPane().add(topMenu, java.awt.BorderLayout.NORTH);
        this.getContentPane().add(myPanel, java.awt.BorderLayout.NORTH);
        btnsPanel.add(btnStart);
        btnsPanel.add(btnStop);
        btnsPanel.add(btnSave);
        btnsPanel.add(btnClear);
        btnsPanel.add(btnExit);
        myPanel.add(chkVisitedURLs, new GridBagConstraints(0, 3, 2, 1, 0.0, 0.0
                , GridBagConstraints.CENTER, GridBagConstraints.NONE,
                new Insets(6, 78, 0, 78), 0, 0));
        myPanel.add(txtPortal, new GridBagConstraints(1, 2, 1, 1, 1.0, 0.0
                , GridBagConstraints.WEST, GridBagConstraints.HORIZONTAL,
                new Insets(7, 0, 0, 130), 0, 0));
        myPanel.add(cmbBoxDomain, new GridBagConstraints(1, 0, 1, 1, 1.0, 0.0
                , GridBagConstraints.CENTER, GridBagConstraints.HORIZONTAL,
                new Insets(4, 0, 0, 130), 78, 0));
        myPanel.add(jLabel2, new GridBagConstraints(0, 0, 1, 1, 0.0, 0.0
                , GridBagConstraints.WEST, GridBagConstraints.NONE,
                new Insets(4, 130, 6, 0), 0, 0));
        myPanel.add(jLabel3, new GridBagConstraints(0, 2, 1, 1, 0.0, 0.0
                , GridBagConstraints.WEST, GridBagConstraints.NONE,
                new Insets(9, 130, 0, 0), 0, 0));
        myPanel.add(txtMaxSites, new GridBagConstraints(1, 1, 1, 1, 1.0, 0.0
                , GridBagConstraints.WEST, GridBagConstraints.HORIZONTAL,
                new Insets(7, 0, 0, 130), 75, 0));
        myPanel.add(jLabel1, new GridBagConstraints(0, 1, 1, 1, 0.0, 0.0
                , GridBagConstraints.WEST, GridBagConstraints.NONE,
                new Insets(6, 130, 0, 0), 0, 0));
        myPanel.add(btnsPanel, new GridBagConstraints(0, 4, 2, 1, 1.0, 1.0
                , GridBagConstraints.SOUTHWEST, GridBagConstraints.BOTH,
                new Insets(0, 46, 0, 50), 17, 3));
        file.add(miRedoClust);
        file.addSeparator();
        file.add(miExit);
        cmbBoxDomain.setPreferredSize(new Dimension(72, 22));
    }

    public void btnExit_actionPerformed(ActionEvent e) {
        //JOptionPane.showMessageDialog(null, "Terminating without saving.");
        System.exit(0);
    }

    public void btnClear_actionPerformed(ActionEvent e) {
        txtMaxSites.setText("100");
        cmbBoxDomain.setSelectedIndex(0);
        txtPortal.setText("http://");
        lstResult.removeAll();
        lstMessages.removeAll();
        chkVisitedURLs.setSelected(false);
        lblStatus.setText("Status Text goes here...");
        PageVisitor.dataSets.clear();
    }

    public void btnStart_actionPerformed(ActionEvent e) {

        //Initialize flag variable of main class, which specifies that either user
        //clicked at Stop or not.
        WebCrawlerMainClass.setStopped(0);

        if(txtMaxSites.getText().length() <=0 || Integer.parseInt(txtMaxSites.getText()) <=0 ) {
           JOptionPane.showMessageDialog(null,
                                         "Please enter valid Max No. of sites to Fetch!");
           return;
       }
        if(txtPortal.getText().length() <= 7) {
            JOptionPane.showMessageDialog(null,
                                          "Please enter valid Portal Site Address!");
            return;
        }

        WebCrawlerMainClass.setStatus("Downloading "+txtPortal.getText()+" ...");
        WebExplorer strt = new WebExplorer();
        strt.run();
    }

    public void txtMaxSites_keyTyped(KeyEvent e) {
        if ( !Character.isDigit(e.getKeyChar()) && !Character.isISOControl(e.getKeyChar()) )
            e.consume();
    }

    public void this_windowClosing(WindowEvent e) {
        //JOptionPane.showMessageDialog(null, "Terminating without saving.");
        System.exit(0);
    }

    public void btnStop_actionPerformed(ActionEvent e) {
        WebCrawlerMainClass.setStatus("Stopping...");
        WebCrawlerMainClass.setStopped(1);
    }

    public void btnSave_actionPerformed(ActionEvent e) {
            WebCrawlerMainClass.clusterNsaveInDatabase(PageVisitor.dataSets);

    }

    public void miExit_mouseClicked(MouseEvent e) {
    }

    public void miRedoClust_mouseClicked(MouseEvent e) {
    }

    public void miRedoClust_actionPerformed(ActionEvent e) {
        //JOptionPane.showMessageDialog(null,"redoing");
        WebCrawlerMainClass.clusterNsaveInDatabase(WebCrawlerMainClass.ExtractAllFromDB());
        //JOptionPane.showMessageDialog(null, "done");
    }

    public void miExit_actionPerformed(ActionEvent e) {
        System.exit(0);
    }
}


class dlgMain_miExit_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_miExit_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.miExit_actionPerformed(e);
    }
}


class dlgMain_miRedoClust_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_miRedoClust_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.miRedoClust_actionPerformed(e);
    }
}


class dlgMain_btnSave_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_btnSave_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.btnSave_actionPerformed(e);
    }
}


class dlgMain_btnStop_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_btnStop_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.btnStop_actionPerformed(e);
    }
}


class dlgMain_this_windowAdapter extends WindowAdapter {
    private dlgMain adaptee;
    dlgMain_this_windowAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void windowClosing(WindowEvent e) {
        adaptee.this_windowClosing(e);
    }
}


class dlgMain_txtMaxSites_keyAdapter extends KeyAdapter {
    private dlgMain adaptee;
    dlgMain_txtMaxSites_keyAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void keyTyped(KeyEvent e) {
        adaptee.txtMaxSites_keyTyped(e);
    }
}


class dlgMain_btnStart_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_btnStart_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.btnStart_actionPerformed(e);
    }
}


class dlgMain_btnClear_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_btnClear_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.btnClear_actionPerformed(e);
    }
}


class dlgMain_btnExit_actionAdapter implements ActionListener {
    private dlgMain adaptee;
    dlgMain_btnExit_actionAdapter(dlgMain adaptee) {
        this.adaptee = adaptee;
    }

    public void actionPerformed(ActionEvent e) {
        adaptee.btnExit_actionPerformed(e);
    }
}
