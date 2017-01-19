<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2014 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *
 */

/**
 * プラグインの基底クラス
 *
 * @package Plugin
 * @author LOCKON CO.,LTD.
 * @version $Id: $
 */
class OrderByPoint extends SC_Plugin_Base
{

    /**
     * コンストラクタ
     *
     * @param  array $arrSelfInfo 自身のプラグイン情報
     * @return void
     */
    public function __construct(array $arrSelfInfo)
    {
        // プラグインを有効化したときの初期設定をココに追加する
        if ($arrSelfInfo["enable"] == 1) {
            
        }
    }

    /**
     * インストール
     * installはプラグインのインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin plugin_infoを元にDBに登録されたプラグイン情報(dtb_plugin)
     * @return void
     */
    public function install($arrPlugin, $objPluginInstaller = null)
    {
        // 管理用のページを配置。 
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$arrPlugin["plugin_code"]}/html/admin/";
        $dest_dir = HTML_REALDIR . ADMIN_DIR;
        SC_Utils::copyDirectory($src_dir, $dest_dir);

    }

    /**
     * アンインストール
     * uninstallはアンインストール時に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function uninstall($arrPlugin, $objPluginInstaller = null)
    {
        // 管理用のページを削除。 
        $target_dir = HTML_REALDIR . ADMIN_DIR;
        $source_dir = PLUGIN_UPLOAD_REALDIR . "{$arrPlugin["plugin_code"]}/html/admin/";
        self::deleteDirectory($target_dir, $source_dir);

    }

    /**
     * 稼働
     * enableはプラグインを有効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function enable($arrPlugin, $objPluginInstaller = null)
    {
        // テンプレートをコピー。
        self::copyTemplate($arrPlugin);
    }

    /**
     * 停止
     * disableはプラグインを無効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function disable($arrPlugin, $objPluginInstaller = null)
    {
        // テンプレートを削除。 
        self::deleteTemplate($arrPlugin);
    }

    /**
     * プラグインヘルパーへ, コールバックメソッドを登録します.
     *
     * @param integer $priority
     */
    public function register(SC_Helper_Plugin $objHelperPlugin, $priority)
    {
        $objHelperPlugin->addAction("prefilterTransform", array(&$this, "prefilterTransform"), $priority);
        $objHelperPlugin->addAction("LC_Page_Products_List_action_after", array(&$this, "products_list_action_after"), $priority);
    }

    /**
     * @param LC_Page_Admin_Products_Product $objPage 商品管理のページクラス
     * @return void
     */
    function products_list_action_after(LC_Page_EX $objPage)
    {
        if ($objPage->orderby != "point")
            return;

        $objProduct = new SC_Product_Ex();

        // パラメーター管理クラス
        $objFormParam = new SC_FormParam_Ex();

        // パラメーター情報の初期化
        $objPage->lfInitParam($objFormParam);

        // 値の設定
        $objFormParam->setParam($_REQUEST);

        // 入力値の変換
        $objFormParam->convParam();

        // 商品一覧データの取得
        $arrSearchCondition = $objPage->lfGetSearchCondition($objPage->arrSearchData);
        $objPage->tpl_linemax = $objPage->lfGetProductAllNum($arrSearchCondition);

        $objPage->objNavi = new SC_PageNavi_Ex($objPage->tpl_pageno, $objPage->tpl_linemax, $objPage->disp_number, 'eccube.movePage', NAVI_PMAX, $urlParam, SC_Display_Ex::detectDevice() !== DEVICE_TYPE_MOBILE);
        $objPage->arrProducts = $this->lfGetProductsList($arrSearchCondition, $objPage->disp_number, $objPage->objNavi->start_row, $objProduct);

        switch ($objPage->getMode()) {
            case 'json':
                $objPage->doJson($objProduct);
                break;

            default:
                $objPage->doDefault($objProduct, $objFormParam);
                break;
        }
    }

    /**
     * 商品一覧の表示
     * 
     * @param SC_Product_Ex $objProduct
     */
    public function lfGetProductsList($searchCondition, $disp_number, $startno, &$objProduct)
    {
        $objQuery = & SC_Query_Ex::getSingletonInstance();

        $arrOrderVal = array();

        $objProduct->setProductsOrder('point_rate', 'dtb_products_class', 'DESC');

        // 取得範囲の指定(開始行番号、行数のセット)
        $objQuery->setLimitOffset($disp_number, $startno);
        $objQuery->setWhere($searchCondition['where']);

        // 表示すべきIDとそのIDの並び順を一気に取得
        $arrProductId = $objProduct->findProductIdsOrder($objQuery, array_merge($searchCondition['arrval'], $arrOrderVal));

        $objQuery = & SC_Query_Ex::getSingletonInstance();
        $arrProducts = $objProduct->getListByProductIds($objQuery, $arrProductId);

        // 規格を設定
        $objProduct->setProductsClassByProductIds($arrProductId);
        $arrProducts['productStatus'] = $objProduct->getProductStatus($arrProductId);

        return $arrProducts;
    }

    /**
     * テンプレートをフックする
     *
     * @param string &$source
     * @param LC_Page_Ex $objPage
     * @param string $filename
     * @return void
     */
    public function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename)
    {
        $objTransform = new SC_Helper_Transform($source);
        switch ($objPage->arrPageLayout['device_type_id']) {
            case DEVICE_TYPE_PC:
                if (strpos($filename, "products/list.tpl") !== false) {
                    $template_path = "products/plg_OrderByPoint_list.tpl";
                    $template = "<!--{include file='{$template_path}'}-->";
                    $objTransform->select(".change")->appendFirst($template);
                }
                break;
            case DEVICE_TYPE_MOBILE:
                break;
            case DEVICE_TYPE_SMARTPHONE:
                break;
            case DEVICE_TYPE_ADMIN:
            default:
                break;
        }
        $source = $objTransform->getHTML();
    }

    /**
     * 指定されたパスを比較して再帰的に削除します。
     * 
     * @param string $target_dir 削除対象のディレクトリ
     * @param string $source_dir 比較対象のディレクトリ
     */
    public static function deleteDirectory($target_dir, $source_dir)
    {
        $dir = opendir($source_dir);
        while ($name = readdir($dir)) {
            if ($name == '.' || $name == '..') {
                continue;
            }
            $target_path = $target_dir . '/' . $name;
            $source_path = $source_dir . '/' . $name;

            if (is_file($source_path)) {
                if (is_file($target_path)) {
                    unlink($target_path);
                    GC_Utils::gfPrintLog("$target_path を削除しました。");
                }
            } elseif (is_dir($source_path)) {
                if (is_dir($target_path)) {
                    self::deleteDirectory($target_path, $source_path);
                }
            }
        }
        closedir($dir);
    }
    
    /**
     * 本体にテンプレートをコピー
     * 
     * @param type $arrPlugin
     */
    public static function copyTemplate($arrPlugin)
    {
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$arrPlugin["plugin_code"]}/data/Smarty/templates/";

        // 管理画面テンプレートを配置。
        $dest_dir = TEMPLATE_ADMIN_REALDIR;
        SC_Utils::copyDirectory($src_dir . "admin/", $dest_dir);

        // PCテンプレートを配置。
        $dest_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC);
        SC_Utils::copyDirectory($src_dir . "default/", $dest_dir);

        // スマホテンプレートを配置。
        $dest_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_SMARTPHONE);
        SC_Utils::copyDirectory($src_dir . "sphone/", $dest_dir);

        // モバイルテンプレートを配置。
        $dest_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_MOBILE);
        SC_Utils::copyDirectory($src_dir . "mobile/", $dest_dir);

    }

    /**
     * 本体にコピーしたテンプレートを削除
     * 
     * @param type $arrPlugin
     */
    public static function deleteTemplate($arrPlugin)
    {
        $src_dir = PLUGIN_UPLOAD_REALDIR . "{$arrPlugin["plugin_code"]}/data/Smarty/templates/";

        // 管理画面テンプレートを削除。 
        $target_dir = TEMPLATE_ADMIN_REALDIR;
        self::deleteDirectory($target_dir, $src_dir . "admin/");

        // PCテンプレートを削除。
        $target_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_PC);
        self::deleteDirectory($target_dir, $src_dir . "default/");

        // スマホテンプレートを削除。
        $target_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_SMARTPHONE);
        self::deleteDirectory($target_dir, $src_dir . "sphone");

        // モバイルテンプレートを削除。
        $target_dir = SC_Helper_PageLayout_Ex::getTemplatePath(DEVICE_TYPE_MOBILE);
        self::deleteDirectory($target_dir, $src_dir . "mobile");

    }  

}
