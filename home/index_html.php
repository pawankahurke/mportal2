<style>
    .main-panel>.content {
    padding: 50px 20px 66px 280px;
    min-height: calc(100vh - 70px);
}
</style>
<div>
    <span id="homeError" style=""></span>
    <input type="hidden" id="dashId" >
    <span class="showErrorImage">
    <img class="" alt="loader..." src="../assets/img/iframefail.png">
    </span>
     <iframe id="Iframe"    style="display:none;" class="kibanahome" data-src="Dashboard/home/index_html.php"></iframe>
    <!-- <div id="Iframe" style="display:none;" class="kibanahome"><b style="padding-top: 19px;"> We apologize for the inconvenience, but we are deploying an update to the reporting module. Our engineers will complete the deployment shortly! We thank you for your patience </b></div> -->

</div>
<style>
    #absoLoader{display:none;width:100%;z-index: 1000;position: relative;left:-8%;height:100%}
    #absoLoader img{margin-top: 20%;}
    /*    #dashName{float: right;font-size: 11px;position: relative;top: 5px;left: -371px;}*/
    #homeError{display:none;}
    /* #Iframe{width:100%; float:left; height:calc(100vh - 101px);} */
    /*#selectedFilter{width: 29%;position: relative;top: 1px;left: 136px;}*/
    #dashdiv{position: relative;top: 21px;min-height:54px;right: 57%;}
    #timediv{position: initial;margin-top: -10px;}
    .selectedfilter{width: 50%;font-family: "Montserrat", sans-serif;font-size: small;border-radius: 4px;}
    .selectedfilter>.bootstrap-select>.dropdown-toggle{width:50%;}
    .bootstrap-select.btn-group .dropdown-menu { min-width: 50%;}
</style>
