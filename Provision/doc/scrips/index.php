<?php

	$title = "Scrip Legend";
   // avoid indavertantly sending output (before HTTP Headers sent)

include_once ( '../../lib/l-util.php'  );

include_once ( '../../lib/l-db.php'    );

include_once ( '../../lib/l-sql.php'   );

include_once ( '../../lib/l-serv.php'  );

include_once ( '../../lib/l-rcmd.php'  );

include_once ( '../../lib/l-head.php'  );





   /*

    |  Main program

    */



    $db = db_connect();

    $authuser = process_login($db);

    $comp = component_installed();



    $msg = ob_get_contents();           // save the buffered output so we can...

    ob_end_clean();                     // (now dump the buffer)

    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);



?>



<link rel="STYLESHEET" href="scrphelp.css" TYPE="text/css" >



<br>



<table border=1 bordercolor="#COCOCO" cellpadding="3" cellspacing="0">



<tr><td>



<p align="center"><strong>Scrip Number</strong>   </TD>

<TD>  </p>



<p align="center"><strong>Scrip Description</strong>    </TD>

<TD>  </p>



<P ALIGN="CENTER"><strong>Detail</strong>     </TD></TR>

<TR><TD></p>

<p>0    </TD>



<TD>  </p>



<p>Client Internal Error Log</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0000"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></A></TD></TR>

<TR><TD>  </p>



<p>6    </TD>



<TD>  </p>



<p>Memory Statistics  </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0006"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>8    </TD>



<TD>  </p>



<p>Orphaned Log File

Sent as

Attachment    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0008"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>9    </TD>



<TD>  </p>



<p>Scandisk Execution    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0009"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>10   </TD>



<TD>  </p>



<p>Scandisk dialog box creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0010"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>12    </TD>



<TD>  </p>



<p>Symantec Virus Definition Management</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0012"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>13    </TD>



<TD>  </p>



<p>Virus Scan Dialog

Box Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0013"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>14    </TD>



<TD>  </p>



<p>MS Internet Account

Dialog Box Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0014"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>15    </TD>



<TD>  </p>



<p>Anti Virus Scan

Execution    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0015"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>16    </TD>



<TD>  </p>



<p>MS Internet Explorer History Folder Dialog Box Creation

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0016"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>17    </TD>



<TD>  </p>



<p>Executable Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0017"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>18    </TD>



<TD>  </p>



<p>ASI Client Shut-down / Re-start</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0018"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>19    </TD>



<TD>  </p>



<p>Disk Defragmenter Execution (MS Windows 9x and Me)

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0019"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>20    </TD>



<TD>  </p>



<p>Disk Defragmenter

Dialog  Box Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0020"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>21    </TD>



<TD>  </p>



<p>File / Folder Deletion Dialog Box Creation

</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0021"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>24    </TD>



<TD>  </p>



<p>Silent.log Check

</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0024"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>26    </TD>



<TD>  </p>



<p>Executable Usage Profiler

</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0026"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>27    </TD>



<TD>  </p>



<p>System Start-up Environment Control

</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0027"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>38    </TD>



<TD>  </p>



<p>Fault Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0038"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>42    </TD>



<TD>  </p>



<p>Password Lockout Resolution    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0042"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>43    </TD>



<TD>  </p>



<p>HandsFree Client Tools    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0043"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>45    </TD>



<TD>  </p>



<p>Port Probe Detected</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0045"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>





<p>46    </TD>



<TD>  </p>



<p>Error Dialog Box

Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0046"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>47    </TD>



<TD>  </p>



<p>Warning Dialog Box

Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0047"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>48    </TD>



<TD>  </p>



<p>Information Dialog Box Creation

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0048"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>49    </TD>



<TD>  </p>



<p>Question Dialog Box

Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0049"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>50    </TD>



<TD>  </p>



<p>Process Creation

Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0050"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>51    </TD>



<TD>  </p>



<p>Process Completion

Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0051"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>60    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0060"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>61    </TD>



<TD>  </p>



<p>System Survey    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0061"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>62    </TD>



<TD>  </p>



<p>Scandisk Files

Clean-up (MS

Windows 9x and Me)  </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0062"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>63    </TD>



<TD>  </p>



<p>ASI Client Start-up</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0063"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>64    </TD>



<TD>  </p>



<p>ASI Client Shut-down</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0064"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>65    </TD>



<TD>  </p>



<p>Traceback Information

on Client Internal Error</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0065"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>68    </TD>



<TD>  </p>



<p>User Has Changed

System Date and Time  </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0068"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>69    </TD>



<TD>  </p>



<p>Chkdsk Files Clean-up (MS Windows NT4, 2000, XP, and Server 2003)</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0069"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>70    </TD>



<TD>  </p>



<p>Software Installation Detected

Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0070"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>71    </TD>



<TD>  </p>



<p>Software Removal Detected

Detected    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0071"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>72    </TD>



<TD>  </p>



<p>Scandisk Log Produced

at Start-up  Found    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0072"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>73    </TD>



<TD>  </p>



<p>System

Restart    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0073"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>74    </TD>



<TD>  </p>



<p>Printer Added /

Removed    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0074"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>76    </TD>



<TD>  </p>



<p>Netscape Preferences

Dialog Box Creation    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0076"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>77    </TD>



<TD>  </p>



<p>Windows Event Log Change Detected

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0077"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>79    </TD>



<TD>  </p>



<p>Eudora Mail Preferences Dialog Box Creation Detected

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0079"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>80    </TD>



<TD>  </p>



<p>Pegasus Mail Preferences Dialog Box Creation Detected

    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0080"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>84    </TD>



<TD>  </p>



<p>Network Connectivity Status</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0084"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>











<TR><TD>  </p>



<p>86    </TD>



<TD>  </p>



<p>Synchronization of

System Clock with

Time Server    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0086"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>87    </TD>



<TD>  </p>



<p>Difference between System and Time Server Clock Exceeds Configured Threshold Since Last Checked Twelve Hours Ago</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0087"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>88    </TD>



<TD>  </p>



<p>Network Devices and Services Availability

</TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0088"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>

<TR><TD>  </p>



<p>89    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0089"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>90    </TD>



<TD>  </p>



<p>McAfee Virus Definition Management</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0090"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









<TR><TD>  </p>







<p>92    </TD>



<TD>  </p>



<p>Disk Defragmenter Execution (MS Windows NT4 and 2000)</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0092"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>93    </TD>



<TD>  </p>



<p>Report Running Processes</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0093"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>94    </TD>



<TD>  </p>



<p>Dialog Box Creation</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0094"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>95    </TD>



<TD>  </p>



<p>Logical Disk Statistics</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0095"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>96    </TD>



<TD>  </p>



<p>Processor Statistics</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0096"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>97    </TD>



<TD>  </p>



<p>Physical Disk Statistics</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0097"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>98    </TD>



<TD>  </p>



<p>Network Statistics</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0098"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







 <TR><TD>  </p>



<p>100   </TD>



<TD>  </p>



<p>

File Distribution and Retrieval

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0100"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







 <TR><TD>  </p>



<p>101   </TD>



<TD>  </p>



<p>

Printer Installation and Removal

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0101"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>111   </TD>



<TD>  </p>



<p>

HandsFree Client Network Deployment

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0111"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>151    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0151"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>152    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0152"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>153    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0153"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>154    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0154"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>155    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0155"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>156    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0156"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>157    </TD>



<TD>  </p>



<p>Scheduled Program

Execution</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0157"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>160   </TD>



<TD>  </p>



<p>

Registry Management

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0160"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>161   </TD>



<TD>  </p>



<p>

Registry Protection Management

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0161"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>164   </TD>



<TD>  </p>



<p>

Email Attachment Filtering Log

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0164"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>165   </TD>



<TD>  </p>



<p>

File Download Filtering Log

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0165"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









<TR><TD>  </p>



<p>174   </TD>



<TD>  </p>



<p>

User Logon-logoff tracking

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0174"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>175   </TD>



<TD>  </p>



<p>

McAfee VirusScan Execution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0175"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









 <TR><TD>  </p>



<p>176   </TD>



<TD>  </p>



<p>

Service Restart (MS Windows NT4, 2000, XP, and Server 2003)

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0176"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>177   </TD>



<TD>  </p>



<p>

Scrip Configuration Update

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0177"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>178   </TD>



<TD>  </p>



<p>

Microsoft Windows Networking Denied Access

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0178"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









<TR><TD>  </p>



<p>187   </TD>



<TD>  </p>



<p>

Machine List Management

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0187"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>











<TR><TD>  </p>



<p>188   </TD>



<TD>  </p>



<p>

Email Attachment Filtering

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0188"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>189   </TD>



<TD>  </p>



<p>

File Download Filtering

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0189"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>191   </TD>



<TD>  </p>



<p>

TCP/IP Connectivity Problem Management

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0191"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>192   </TD>



<TD>  </p>



<p>

Program Execution Control

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0192"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>196   </TD>



<TD>  </p>



<p>

Software Patch Application

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0196"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>197   </TD>



<TD>  </p>



<p>

Network Configuration Change Detected

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0197"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>199   </TD>



<TD>  </p>



<p>

Registry Change Detected

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0199"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>201   </TD>



<TD>  </p>



<p>

Network Device Driver Management

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0201"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>207   </TD>



<TD>  </p>



<p>

Content Distribution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0207"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









<TR><TD>  </p>



<p>208   </TD>



<TD>  </p>



<p>

Software Update

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0208"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>









<TR><TD>  </p>



<p>211   </TD>



<TD>  </p>



<p>

Disk Defragmenter Execution (MS Windows XP and Server 2003)

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0211"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>212   </TD>



<TD>  </p>



<p>

Scheduled Program Execution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0212"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>



<TR><TD>  </p>



<p>213   </TD>



<TD>  </p>



<p>

Scheduled Program Execution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0213"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>214   </TD>



<TD>  </p>



<p>

Scheduled Program Execution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0214"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>215   </TD>



<TD>  </p>



<p>

Scheduled Program Execution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0215"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>





<TR><TD>  </p>



<p>216   </TD>



<TD>  </p>



<p>

Print Queue Problem Resolution

</TD>



<td>



</p>





<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0216"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></p></TD></TR>







<TR><TD>  </p>



<p>217    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0217"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>218    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0218"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>219    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0219"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>220    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0220"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>221    </TD>



<TD>  </p>



<p>Clean Folders    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0221"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>222    </TD>



<TD>  </p>



<p>Report File Attributes    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0222"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>224    </TD>



<TD>  </p>



<p>MS Windows Service Manager   </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0224"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>





<p>225    </TD>



<TD>  </p>



<p>Directory and File Protection Management   </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0225"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>227    </TD>



<TD>  </p>



<p>Process and Service Shutdown-Restart    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0227"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>228    </TD>



<TD>  </p>



<p>Network Packet Filtering    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0228"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>











<TR><TD>  </p>



<p>229    </TD>



<TD>  </p>



<p>Application Provisioning    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0229"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>230    </TD>



<TD>  </p>



<p>Application Metering    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0230"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>231    </TD>



<TD>  </p>



<p>Client Heartbeat    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0231"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>232    </TD>



<TD>  </p>



<p>Intrusion Protection Control    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0232"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>233    </TD>



<TD>  </p>



<p>System Start-up Environment Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0233"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>236    </TD>



<TD>  </p>



<p>On-demand Remote Control    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0236"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>237    </TD>



<TD>  </p>



<p>Microsoft Windows Update Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0237"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>238    </TD>



<TD>  </p>



<p>Symantec Anti Virus Definition Dates Log    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0238"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>239    </TD>



<TD>  </p>



<p>McAfee Anti Virus Definition Dates Log    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0239"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>240    </TD>



<TD>  </p>



<p>Intrusion Protection Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0240"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>







<TR><TD>  </p>



<p>241    </TD>



<TD>  </p>



<p>Contact Information    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0241"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>242    </TD>



<TD>  </p>



<p>eTrust Virus Definition Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0242"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



<TR><TD>  </p>



<p>243    </TD>



<TD>  </p>



<p>eTrust EZ Antivirus Scan Execution    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0243"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>





<TR><TD>  </p>



<p>244    </TD>



<TD>  </p>



<p>eTrust EZ Antivirus Definition Dates Log    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0244"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>245    </TD>



<TD>  </p>



<p>On-demand GoToAssist    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0245"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>



















<TR><TD>  </p>



<p>246    </TD>



<TD>  </p>



<p>Network Device Discovery    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0246"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>247    </TD>



<TD>  </p>



<p>Trend Micro Virus Definition Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0247"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>248    </TD>



<TD>  </p>



<p>Registry Device Driver Management    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0248"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>249    </TD>



<TD>  </p>



<p>Trend Micro anti virus Scan Execution    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0249"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









<TR><TD>  </p>



<p>250    </TD>



<TD>  </p>



<p>Trend Micro anti virus Definition Dates Log    </TD>

<td>



</p>



<P ALIGN="CENTER"><A HREF="scrip.php?scrip=0250"><img src="detail.gif" width="33" height="14" border=0 alt="Scrip detail information"></a></TD></TR>









</table>







<?php

    echo head_standard_html_footer($authuser,$db);

?>





