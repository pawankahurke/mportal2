<?

/* 
Revision history:

Date        Who     What
----        ---     ----
15-Sep-03   NL      Creation.
16-Sep-03   NL      Add style for bullet list (square).
16-Sep-03   NL      Remove text-align: justify
17-Sep-03   NL      Add onLoad="window.focus()" to BODY tag.
*/


function html_header()
{ 
    $msg = <<< HERE

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
	<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=windows-1252">
	<TITLE>Help</TITLE>
	<STYLE>
	<!--
		@page { size: 8.5in 11in; margin-right: 0.69in; margin-top: 1in; margin-bottom: 0.67in }
		P { margin-left: 0.75in; margin-bottom: 0.17in; direction: ltr; color: #000000; line-height: 0.17in; widows: 2; orphans: 2 }
		P.western { font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US }
		P.cjk { font-family: "Times New Roman", serif; font-size: 10pt }
		P.ctl { font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA }
		P.list-western { margin-left: 1in; text-indent: -0.25in; font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US }
		P.list-cjk { margin-left: 1in; text-indent: -0.25in; font-family: "Times New Roman", serif; font-size: 10pt }
		P.list-ctl { margin-left: 1in; text-indent: -0.25in; font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA }
		H2 { margin-top: 0in; margin-bottom: 0.17in; direction: ltr; color: #000000; line-height: 0.17in; page-break-inside: avoid; widows: 2; orphans: 2 }
		H2.western { font-family: "Arial Black", sans-serif; font-size: 11pt; so-language: en-US; font-weight: medium }
		H2.cjk { font-family: "Times New Roman", serif; font-size: 11pt; font-weight: medium }
		H2.ctl { font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA; font-weight: medium }
		H3 { margin-top: 0in; margin-bottom: 0.17in; direction: ltr; color: #000000; line-height: 0.17in; page-break-inside: avoid; widows: 2; orphans: 2 }
		H3.western { font-family: "Arial Black", sans-serif; font-size: 10pt; so-language: en-US; font-weight: medium }
		H3.cjk { font-family: "Times New Roman", serif; font-size: 10pt; font-weight: medium }
		H3.ctl { font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA; font-weight: medium }
		P.text-body-indent-western { margin-left: 1in; font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US }
		P.text-body-indent-cjk { margin-left: 1in; font-family: "Times New Roman", serif; font-size: 10pt }
		P.text-body-indent-ctl { margin-left: 1in; font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA }
		P.ww-list-bullet1-western { font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US; page-break-inside: avoid }
		P.ww-list-bullet1-cjk { font-family: "Times New Roman", serif; font-size: 10pt; page-break-inside: avoid }
		P.ww-list-bullet1-ctl { font-family: "Arial", sans-serif; font-size: 7pt; so-language: ar-SA; page-break-inside: avoid }
		P.ww-list-bullet-21-western { color: #000000; font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US; page-break-inside: avoid }
		P.ww-list-bullet-21-cjk { color: #000000; font-family: "Times New Roman", serif; font-size: 10pt; page-break-inside: avoid }
		P.ww-list-bullet-21-ctl { color: #000000; font-family: "Arial", sans-serif; font-size: 7pt; so-language: ar-SA; page-break-inside: avoid }
		P.ww-list-number1-western { font-family: "Arial", sans-serif; font-size: 10pt; so-language: en-US }
		P.ww-list-number1-cjk { font-family: "Times New Roman", serif; font-size: 10pt }
		P.ww-list-number1-ctl { font-family: "Times New Roman", serif; font-size: 10pt; so-language: ar-SA }
		A:link { color: #0000ff }
		A:visited { color: #800080 }
        LI {list-style : square;}
	-->
	</STYLE>
</HEAD>
<BODY LANG="en-US" TEXT="#000000" LINK="#0000ff" VLINK="#800080" DIR="LTR" onLoad="window.focus()">

HERE;

    echo $msg;
}

?>