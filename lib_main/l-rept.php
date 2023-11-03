<?php




define('constReptNameParam',                'name');
define('constReptFileNameParam',            'fname');
define('constReptDestEmailParam',           'email');
define('constReptDestDisplayParam',         'display');
define('constReptDestInfoPortalParam',      'infoport');
define('constReptEmailListParam',           'elist');
define('constReptEmailDefaultParam',        'emaildef');
define('constReptOutputFormatParam',        'outform');
define('constReptEmailBodyTextParam',       'btext');
define('constReptEmailSubjectParam',        'subject');
define('constReptEmailAttachFileNameParam', 'attfname');
define('constReptEmailFromParam',           'efrom');
define('constReptEmailAttachBegin',         'ebegin');
define('constReptEmailAttachEnd',           'eend');


define('constReptCompDbNameParam',      'dbname');
define('constReptCompTableNameParam',   'tname');
define('constReptCompHeaderParam',      'header');
define('constReptCompTableFormatParam', 'tformat');
define('constReptCompWhereParam',       'where');
define('constReptCompLimitParam',       'limit');
define('constReptCompGroupParam',       'group');
define('constReptCompOrderParam',       'order');
define('constReptCompNumGroupParam',    'ngroup');
define('constReptCompRollupParam',      'rollup');


define('constReptCompColDbNameParam',           'dbname');
define('constReptCompColTdOptionsParam',        'tdoptions');
define('constReptCompColTdHOptionsParam',       'tdhoptions');
define('constReptCompColColIndexParam',         'index');
define('constReptCompColColNameParam',          'name');
define('constReptCompColColFormatParam',        'format');
define('constReptCompColNameFormatParam',       'nformat');
define('constReptCompColLocationParam',         'location');
define('constReptCompColLocationFormatParam',   'locformat');
define('constReptCompColLocationGrpIdxParam',   'grpidx');
define('constReptCompColLocationSepParam',      'sep');
define('constReptCompColNullHandleParam',       'nhandle');


define('constReptOutputFormatHTML',     1);


define('constReptLocFormatHigh',        1);
define('constReptLocFormatWide',        2);
define('constReptLocFormatNormal',      3);
define('constReptLocFormatDbValue',     4);


define('constReptColFormatNone',            0);
define('constReptColFormatHeader',          1);
define('constReptColFormatBold',            2);
define('constReptColFormatBoldIndent',      3);
define('constReptColFormatNoneIndent',      4);
define('constReptColFormatNoneT',           5);
define('constReptColFormatNoneIndentT',     6);
define('constReptColFormatBoldT',           7);
define('constReptColFormatBoldIndentT',     8);
define('constReptColFormatHeaderT',         9);


define('constNullHandleValue',              'nv');
define('constNullDisableHeader',            'd');
define('constNullHandleSep',                's');


define('constReptColMapGroupAscStr',        '1');
define('constSpecialOptNoneGroup',          '0_0_0');


function REPT_AddComponent(
    $myReport,
    $dbName,
    $tableName,
    $header,
    $tableFormat,
    $where,
    $limit,
    $group,
    $order,
    $numGroup,
    $rollup
) {
    if (PHP_ALST_MakeAList(CUR, $params) != constAppNoErr) {
        REPT_PrintError();
        return -1;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptCompDbNameParam,
        $dbName
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptCompTableNameParam,
        $tableName
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (strcmp($header, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompHeaderParam,
            $header
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($tableFormat, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompTableFormatParam,
            $tableFormat
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($where, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompWhereParam,
            $where
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($limit, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompLimitParam,
            $limit
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($group, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompGroupParam,
            $group
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($order, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompOrderParam,
            $order
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptCompNumGroupParam,
        $numGroup
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }

    $numRollup = 0;
    if ($rollup) {
        $numRollup = 1;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptCompRollupParam,
        $numRollup
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }

    $compIdx = -1;
    if (PHP_REPT_AddReportComponentParam(
        CUR,
        $compIdx,
        $myReport,
        $params
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }

    if (PHP_ALST_FreeEntireAList(CUR, $params) != constAppNoErr) {
        REPT_PrintError();
        return -1;
    }

    return $compIdx;
}



function REPT_AddComponentCol(
    $thisReport,
    $compIdx,
    $dbName,
    $colIndex,
    $colName,
    $colFormat,
    $colNameFormat,
    $colLocation,
    $colLocationFormat,
    $colLocationGrpIdx,
    $tdOptions,
    $tdHOptions,
    $sep,
    $nullHandleList
) {
    if (PHP_ALST_MakeAList(CUR, $params) != constAppNoErr) {
        REPT_PrintError();
        return -1;
    }
    if (strcmp($dbName, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompColDbNameParam,
            $dbName
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($tdOptions, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompColTdOptionsParam,
            $tdOptions
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($tdHOptions, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompColTdHOptionsParam,
            $tdHOptions
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if ($colIndex != -1) {
        if (PHP_ALST_SetNamedItemUInt32(
            CUR,
            $params,
            constReptCompColColIndexParam,
            $colIndex
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptCompColColNameParam,
        $colName
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptCompColColFormatParam,
        $colFormat
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptCompColNameFormatParam,
        $colNameFormat
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptCompColLocationParam,
        $colLocation
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if (
        PHP_ALST_SetNamedItemUInt32(
            CUR,
            $params,
            constReptCompColLocationFormatParam,
            $colLocationFormat
        )
        != constAppNoErr
    ) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }
    if ($colLocationGrpIdx != -1) {
        if (
            PHP_ALST_SetNamedItemUInt32(
                CUR,
                $params,
                constReptCompColLocationGrpIdxParam,
                $colLocationGrpIdx
            )
            != constAppNoErr
        ) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if (strcmp($sep, '') != 0) {
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $params,
            constReptCompColLocationSepParam,
            $sep
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }
    if ($nullHandleList != 0) {
        if (
            PHP_ALST_SetNamedItemAList(
                CUR,
                $params,
                constReptCompColNullHandleParam,
                $nullHandleList
            )
            != constAppNoErr
        ) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $params);
            return -1;
        }
    }

    if (PHP_REPT_AddReportComponentColParam(
        CUR,
        $thisReport,
        $compIdx,
        $params
    ) != constAppNoErr) {
        REPT_PrintError();
        PHP_ALST_FreeEntireAList(CUR, $params);
        return -1;
    }

    if (PHP_ALST_FreeEntireAList(CUR, $params) != constAppNoErr) {
        REPT_PrintError();
        return -1;
    }

    return 0;
}



function REPT_CreateNullHandleList($myArray)
{
    if (PHP_ALST_MakeAList(CUR, $fullList) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    foreach ($myArray as $key => $row) {
        if (PHP_ALST_MakeAList(CUR, $thisNull) != constAppNoErr) {
            REPT_PrintError();
            return 0;
        }
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $thisNull,
            constNullHandleValue,
            $row['value']
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $thisNull);
            return 0;
        }
        if (PHP_ALST_SetNamedItemUInt32(
            CUR,
            $thisNull,
            constNullDisableHeader,
            $row['dheader']
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $thisNull);
            return 0;
        }
        if (PHP_ALST_SetNamedItemString(
            CUR,
            $thisNull,
            constNullHandleSep,
            $row['sep']
        ) != constAppNoErr) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $thisNull);
            return 0;
        }
        if (
            PHP_ALST_SetNamedItemAList(CUR, $fullList, strval($key), $thisNull)
            != constAppNoErr
        ) {
            REPT_PrintError();
            PHP_ALST_FreeEntireAList(CUR, $thisNull);
            return 0;
        }
    }

    return $fullList;
}



function REPT_PrintError()
{
    echo "An error has occurred processing this page.  Click "
        . "<a href=\"../acct/csrv.php?error\">here</a> for details.";
}



function REPT_MakeMUMSectionArray($db, $username)
{
    $qu = safe_addslashes($username);
    echo '<script type="text/javascript" language="JavaScript">';
    echo 'mumSectionsArray = new Array();';
    echo 'mumSectionsArray.push(Array(\'None\',\'\'));';
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = 'SELECT S.sectionuniq, S.sectionname FROM Section AS S LEFT JOIN '
        . 'Section AS X ON (X.sectionname=S.sectionname AND X.global=0 AND '
        . "X.username='$qu') WHERE (S.username='$qu' OR (S.global=1 AND "
        . 'X.sectionid IS NULL)) AND S.sectiontype=' . constSectionTypeMUM
        . ' ORDER BY CONVERT(S.sectionname USING latin1)';
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        echo 'mumSectionsArray.push(Array(\''
            . safe_addslashes($row['sectionname']) . '\',\''
            . $row['sectionuniq'] . '\'));';
    }
    echo '</script>';
}
