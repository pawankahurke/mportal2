 var ConfigObj = {  
    "installer":{  
       "termsURL":"https://www.nanoheal.com",
       "textChanges":{  
          "termsText":"Terms and Conditions",
          "ins_home_title":"Welcome to Nanoheal Client Setup",
          "ins_home_desc":"We keep your connected ecosystem humming along - so you don't have to. Our experts and tools will help ensure your devices are safe, clean and running fast.",
          "termsAgreeText":"I agree to the Terms and Conditions associated with this product",
          "ins_progress_title":"Nanoheal Client is installing. Please wait...",
          "ins_progress_desc":"Installing Nanoheal Client.",
          "ins_uninstall_title":"Nanoheal Client is uninstalling. Please wait...",
          "ins_uninstall_desc":"Uninstalling Nanoheal Client...",
          "ins_finish_title":"Congratulations!",
          "ins_finish_desc":"Nanoheal Client is successfully installed your system. Please click on Finish to close the installer and launch the desktop app.",
          "ins_finish_btn":"Finish"
       }
    },
    initLoader:{
        image: "../../../../ui/pub/images/loader-lg.gif",
        message:"Please wait while we are getting ready...."        
    },
    dropDownList:{
        Settings:"Settings",
        SystemInfo:"System Information",
        ServiceLogs:"Service Logs",
        Troubleshooters:"Troubleshooters"
    },
     errorMessages : {
        scanFail :{
            title:'Scan Failed',
            desc:'Scan request could not be completed.'
        },
        fixFail:{
            title:'Fix Failed',
            desc:'Issue resolution failed due to an internal error.'
        },
        zeroRecord:{
           title:'No entry selected!',
           desc:'Please select at-least one entry from the list and try again.'
        },
        inExecution:{
            title:'Warning!',
            desc:'Previous process is in execution. Please try after some time.'
        },
        unexpectedError:{
            title:'Warning!',
            desc:'Unexpected error occurred. Please try again later or restart your system'
        },
        otherTileRunning:{
            title:'Warning!',
            desc:'Previous process is in execution. Please try after some time.'
        },
        openChatUrlAsPopup:{
            title:'Warning!',
            desc:'Unexpected error occurred. Please try after some time.'
        }
    },
    "scanningPageImages":[  
       {  
          "img":"../../../../ui/pub/images/group-1.png",
          "message":"Getting you setup usual takes just a few minutes - it's worth the wait!"
       },
       {  
          "img":"../../../../ui/pub/images/group-2.png",
          "message":"Most of our fixes take just a second or two - if only everything could be repaired so easily."
       },
       {  
          "img":"../../../../ui/pub/images/group-3.png",
          "message":"Did you know that Nanoheal can fix issues before you are even aware of them?"
       }
    ],
    "lifelinedata":{  
       "ValidationDate":"14",
       "timetext":"Monday to Friday, 7AM to 7PM CST. Estimated Turnaround time would be 2-3 hours.",
       "timeDiv":"yes",
       "activityText":"Person ID",
       "activityOption":"yes",
       "lifelineOption":"yes",
       "callbackSuccessMsg":"Call Back request sent Successfully. We will call you back within 24 hours.",
       "sendTicketSuccessMsg":"Email request sent successfully.",
       "lifelineLocation":"no",
       "lifelineLocArr":"BLR,MGR",
       "config_lifeline":"Connect to Nanoheal",
       "config_footerText":"Lifeline",
       "config_callmeBack":"Call Me Back",
       "config_callbackDesc":"Request a call back, our representative will reach you.",
       "config_sendTicket":"Send Ticket",
       "config_sendticketDesc":"Please reach us on email outside business hours, we will attend to it at the earliest.",
       "config_llHeadLine":"Please contact our support team, we are here to help you.",
       "config_headLine_flag":"yes",
       "config_alertHeading":"Nanoheal",
       "lifelineAct_varvalue":"lifelineActivated",
       "lifelineCust_varvalue":"lifelineUsed",
       "activityError":"Please enter Person ID ",
       "config_isTimeStamp":"yes",
       "config_timeStampData":"PST,MST,CST,EST",
       "config_isIssueRelated":"yes",
       "config_issueRelatedData":"Antivirus/Antispyware Related,Browser Related,CD/DVD Issues,Displayed Error Messages,Hard Drive/Memory Related,Internet Connection,Slow Performance,Windows Update,Windows Upgrade",
       "config_ProgressBar":"yes",
       "lifelineInputField":"yes",
       "LocationData":{  
          "US":[  
             {  
                "msg":"MSG1",
                "loc":"BLR"
             },
             {  
                "msg":"MSG1",
                "loc":"GUR"
             },
             {  
                "msg":"MSG2",
                "loc":"HYD"
             }
          ],
          "EMIA":[  
             {  
                "msg":"MSG3",
                "loc":"BLR"
             },
             {  
                "msg":"MSG3",
                "loc":"GUR"
             }
          ],
          "MSG":{  
             "DEFAULT":"defaultMessage",
             "MSG1":"abcd1",
             "MSG2":"abcd2",
             "MSG3":"sdf"
          }
       }
    },
    "onlyTSPage":"yes",
    "companyName":"Nanoheal",
    "trailTitle1":"Welcome to Nanoheal!",
    "trailTitle2":"Go beyond seeing a problem and fix it with automation. Click the button below to go to troubleshooter.",
    "proTitle1":"Welcome to Nanoheal!",
    "proTitle2":"Go beyond seeing a problem and fix it with automation. Click the button below to go to troubleshooter.",
    "commonTileDesc":"Listed below are Nanoheal standard fixes for the most common issues with your device. The Solutions listed have been tested and safe to use on your Nanoheal device. Choose the category on left or below, and then select the fix that matches the symptoms of the problem.",
    "showUpdateBtn":"yes",
    "showRefreshBtn":"yes",
    "showUpgradeOption":"no",
    "showLandingTSBtn":"yes",
    "surveylink":"",
    "showADInfo":"no",
    "showSummaryViewFixBtn":"no",
    "showLeftToolboxTiles":"no",
    "DataLog":"Tools\\Logs\\data.txt",
    "SKUDetails":"",
    "chatData":{  
       "chatType":"APP",
       "chatCmd":"powershell%20-executionpolicy%20unrestricted%20-windows%20hidden%20C%3A%5CProgramData%5Claunchurl.PS1",
       "chatPopupHeight":"500",
       "chatPopupWidth":"400",
       "configAppDetails":{  
          "name":"Nanoheal.com Remote Support",
          "o64":"\"C:\\Program Files (x86)\\LogMeIn Rescue Calling Card\\awppuu\\CallingCard.exe\"",
          "o32":"\"C:\\Program Files\\LogMeIn Rescue Calling Card\\awppuu\\CallingCard.exe\"",
          "successHeader":"LMI Session Initiated",
          "successDesc":"Please click on the flashing icon at the bottom of your screen, Then click \"OK\" to continue.",
          "noAppMsg":"Please use the Troubleshooter menu to install LogMeIn Calling Card."
       }
    },
    "completeScanData":{  
       "scanType":"AUTOSCAN",
       "varValue":"COMPLETESCAN",
       "profiles":[  
          "Browser Issues",
          "Junk file Clean up",
          "Application Installed",
          "Start-up Services"
       ]
    },
    "settingsPageItems":{  
       "SystemSerialNoTileName":"System Serial No",
       "CaseNoTileName":"Case No",
       "SiteNameTileName":"Site Name",
       "ClientVersionTileName":"Client Version",
       "ServiceUntilTileName":"Expiration Date",
       "realTimeProtectionTileName":""
    },
    "threshold":"",
    "CommonTsTiles":"",
    "expiryPageData":"",
    "textChanges":{  
       "headerMenu":{  
          "settings":"Settings",
          "systeminfo":"System Information",
          "servicelogs":"Service Logs",
          "troubleshooters":"Troubleshooters",
          "purchase":""
       },
       "footer":{  
          "needHelp":"Need Help",
          "liveChat":"Live Chat"
       },
       "landingPage":{  
          "landingScanBtn":"Start Your System Scan",
          "landingTroubleshooterBtn":"Click To Troubleshoot"
       },
       "summaryPage":{  
          "storageWasted":"storage wasted",
          "unwantedRegistries":"unwanted registries",
          "outdatedApps":"outdated apps",
          "startupTime":"startup time",
          "installedApps":"installed apps",
          "malwareVirusMsg":"virus infection cleared",
          "afterFixStorageWasted":"storage recovered",
          "afterFixUnwantedRegistries":"unwanted registries removed",
          "afterFixOutdatedApps":"outdated apps",
          "afterFixStartupTime":"startup time",
          "afterFixInstalledApps":"installed apps",
          "afterFixMalwareVirusMsg":"virus infection cleared",
          "troubleshooterBtnDesc":"If the issue you are having is not addressed, please click####here####to view all our solutions.",
          "systemStatus":"Your System Status",
          "foundIssues":"We found the following issues"
       },
       "settingsPage":{  
          "settingSkuName":"",
          "settingsTitle":"Settings",
          "licenseInfo":"Your License Information",
          "systemSerialNoTileName":"SYSTEM SERIAL NO.",
          "caseNoTileName":"CASE NO.",
          "siteNameTileName":"SITE NAME",
          "clientVersionTileName":"CLIENT VERSION",
          "serviceUntilTileName":"EXPIRATION DATE",
          "updateBtnName":"",
          "refreshBtnName":""
       },
       "sysInfoPage":{  
          "sysInfoTitle":"System Information",
          "softwareInfo":"Software Information",
          "hardwareInfo":"Hardware Information",
          "brand":"BRAND",
          "model":"MODEL",
          "serialNumber":"SERIAL NUMBER",
          "os":"OS",
          "primaryPartition":"PRIMARY PARTITION",
          "systemMemory":"SYSTEM MEMORY",
          "processor":"PROCESSOR",
          "graphicsCard":"GRAPHICS CARD",
          "computerName":"COMPUTER NAME",
          "domainName":"DOMAIN NAME",
          "userName":"USER NAME",
          "ipAddress":"IP ADDRESS",
          "activeDirectory Server":"ACTIVE DIRECTORY SERVER",
          "assetID":"ASSET ID"
       },
       "serviceLogPage":{  
          "serviceLogTitle":"Service Logs",
          "log":"Log",
          "date":"Date",
          "noLogs":"No service logs found",
          "allServiceTypes":"All Service Types",
          "scheduledMaintenance":"Scheduled Maintenance",
          "toolboxFix":"Toolbox Fix",
          "alert":"Alert",
          "selfHealingFix":"Self-Healing Fix",
          "liveHelp":"Live Help",
          "historyDetails":"History Details"
       },
       "viewfixPage":{  
          "viewAndFixTitle":"View and Fix",
          "freeFixTitle":"Fix for Free",
          "purchaseToFixTitle":"Purchase to Fix",
          "fixBtnName":"Repair My System",
          "purchaseBtnName":"PURCHASE TO FIX",
          "visitTSPage":"If the issue you are having is not addressed, please click####here####to view all our solutions."
       },
       "troubleshooterPage":{  
          "tsTitle":"Troubleshooters",
          "fixBtnName":"RUN THIS REPAIR",
          "purchaseBtnName":"PURCHASE TO FIX"
       },
       "freeFixPage":{  
          "surveyMsg":"If the issue you're having is not addressed, click below to browse our troubleshooters.####Take our survey to let us know how we did."
       },
       "scanningPage":{  
          "scanTitle":"Please wait...",
          "scanDesc":"It may take a few minutes to scan your system. If the scan takes longer than 25 minutes please contact an agent via Live Chat.",
          "repairTitle":"Repairing your system",
          "repairDesc":"It might take few minutes to repair the issues on your system",
          "timeElapsedText":"Time elapsed"
       },
       "signinpage":{  
          "title":"Welcome to Nanoheal!",
          "enterprise":{  
             "maindesc":"Please enter your customer number to proceed",
             "placeholder":"12345678",
             "tooltip_title":"Why do we need your customer number?",
             "tooltip_desc":"To check......................."
          },
          "msp":{  
             "maindesc":"Please enter your email address to proceed",
             "placeholder":"example@gmail.com",
             "tooltip_title":"Why do we need your email address?",
             "tooltip_desc":"A lot of what Nanoheal fixes on your system is completed silently in the background as part of our proactive support, but there are times we want to tell you about an issue, or give you a health report so you can see exactly what is happening inside there! We ask for your email address to allow us inform you about the health of your system,our great offers and important updates. Nothing less, nothing more."
          },
          "pts":{  
             "maindesc":"Please enter your email address to proceed",
             "placeholder":"example@gmail.com",
             "tooltip_title":"Why do we need your email address?",
             "tooltip_desc":"A lot of what Nanoheal fixes on your system is completed silently in the background as part of our proactive support, but there are times we want to tell you about an issue, or give you a health report so you can see exactly what is happening inside there! We ask for your email address to allow us inform you about the health of your system,our great offers and important updates. Nothing less, nothing more."
          }
       },
       "registerPage":{  
          "passwordTitle":"Confirm Password Security",
          "passwordDesc":"Your password must be a minimum of 8 characters and contain at least one number and one upper case letter or symbol."
       }
    },
    "antiVirusData":"",
    "uninstallData":"",
    "completeFixData":"",
    "Languages":""
 };