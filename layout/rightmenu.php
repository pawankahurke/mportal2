 <script src="https://<?php echo $_SERVER['HTTP_HOST'] ?>/Dashboard/js/home/home.js"></script>



<div id="rsc-add-container3" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <div class="button col-md-12 text-left pt-1 pb-1 pl-0">
            <button class="swal2-confirm btn btn-success btn-sm btn-round" id="site_button" aria-label="" style="width: 70.64px;">Sites</button>
            <?php
               $url = $_SERVER['REQUEST_URI'];
               $url = explode('?', $url);
               $url = $url[0];
               if (!strripos($url, "census")) {
               ?>
                <button class="swal2-confirm btn btn-success btn-sm btn-simple" id="group_button" aria-label="">Groups</button>
            <?php
               }
               ?>

            <i id="sm-search-show" class="tim-icons icon-zoom-split"></i>
        </div>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container3">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle" id="save_rightMenu">
            <div class="toolTip" data-qa="saveSelectState" onclick="saveSelectState();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <input type="hidden" id="searchType" name="searchType" value="<?php echo url::toText($_SESSION['searchType']); ?>" />
    <input type="hidden" id="searchValue" name="searchValue" value="<?php echo url::toText($_SESSION['searchValue']); ?>" />
    <input type="hidden" id="rparentName" name="rparentName" value="<?php echo url::toText($_SESSION['rparentName']); ?>" />
    <input type="hidden" id="passlevel" name="passlevel" value="<?php echo url::toText($_SESSION['passlevel']); ?>" />
    <input type="hidden" id="rcensusId" name="rcensusId" value="<?php echo url::toText($_SESSION['rcensusId']); ?>" />
    <input type="hidden" id="currentwindow" name="currentwindow" value="<?php echo url::toText($_SESSION['currentwindow']); ?>" />
    <input type="hidden" id="searchLabel" name="searchLabel" value="" />
    <input type="hidden" name="jsCallback" value="" />
    <input type="hidden" id="selectedtag" value="">
    <input type="hidden" id="lastmacClick" value="">
    <input type="hidden" id="lastmacLimit" value="100">
    <input type="hidden" id="lastSelectId" value="">
    <div class="form table-responsive white-content mt-4">
        <div class="sidebar" id="sites">
            <div class="rm-search-parent" style="display:none;">
                <input type="text" data-bs-target="mainulSites" class="nhl-htm-search-box form-control" autocomplete="off" role="textbox" aria-label="Search">
            </div>
            <ul class="nav" id="mainulSites"></ul>
        </div>
        <div class="sidebar" id="groups" style="display:none !important;">
            <div class="rm-search-parent" style="display:none;">
                <input type="text" data-bs-target="mainulGroups" class="nhl-htm-search-box form-control" autocomplete="off" role="textbox" aria-label="Search">
            </div>
            <ul class="nav" id="mainulGroups"></ul>
        </div>
    </div>
</div>

<style>
    .nhl-htm-search-box {
        width: 89% !important;
        margin-left: 17px;
    }

    #sm-search-show {
        cursor: pointer
    }

    .rm-container-search-adjust {
        position: relative;
        top: 46px;
    }

    .rm-search-parent {
        width: 30%;
        height: auto;
        padding: 10px 0;
        position: fixed;
        background-color: #fff;
        z-index: 1000;
    }

    .rm-adjust-height {
        padding-top: 46px;
        z-index: 100;
    }

    #site_button {
        margin-left: -2px;
    }

    .tim-icons .icon-simple-add {
        color: black;
    }

   #lastSelectId + .form.table-responsive.white-content.mt-4.ps.ps--active-y {
       overflow-y: scroll !important;
   }

   #lastSelectId + .form.table-responsive.white-content.mt-4.ps.ps--active-y::-webkit-scrollbar {
       width: 0.4em;
   }
   #lastSelectId + .form.table-responsive.white-content.mt-4.ps.ps--active-y .ps__rail-y{
       display: none !important;
   }
</style>
