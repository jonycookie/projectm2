<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9
//******************************************************//
// IceBB integration module
// $Id: index.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//
// ICEBB IS FREE SOFTWARE.
// http://icebb.net/license/
//******************************************************//

define('PATH_TO_ICEBB'			, '');
require(PATH_TO_ICEBB.'icebb.php');
$icebb_instance					= new icebb();
$icebb_instance->path_to_icebb	= '';
$icebb_instance->url_to_icebb	= '';
$icebb_instance->init();
//echo $icebb->output;
?>