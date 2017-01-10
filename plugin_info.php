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
 * プラグインの情報クラス
 *
 * @package Plugin
 * @author LOCKON CO.,LTD.
 * @version $Id: $
 */
class plugin_info
{
    /** プラグインコード(必須)：システム上でのキーとなります。プラグインコードは一意である必要があります。 */
    static $PLUGIN_CODE = "OrderByPoint";

    /** プラグイン名(必須)：プラグイン管理・画面出力（エラーメッセージetc）にはこの値が出力されます。 */
    static $PLUGIN_NAME = "商品一覧ページをポイント順で並べ替え";

    /** プラグインメインクラス名(必須)：本体がプラグインを実行する際に呼ばれるクラス。拡張子は不要です。 */
    static $CLASS_NAME = "OrderByPoint";

    /** プラグインバージョン(必須) */
    static $PLUGIN_VERSION = "1.0.0";

    /** 本体対応バージョン(必須) */
    static $COMPLIANT_VERSION = "2.13";

    /** 作者(必須) */
    static $AUTHOR = "kurozumi";

    /** 説明(必須) */
    static $DESCRIPTION = "商品一覧ページをポイント順で並べ替えができるプラグイン。";

    /** 作者用のサイトURL：設定されている場合はプラグイン管理画面の作者名がリンクになります。 */
    static $AUTHOR_SITE_URL = "http://a-zumi.net";

    /** プラグインのサイトURL : 設定されている場合はプラグイン管理画面の作者名がリンクになります。 */
    static $PLUGIN_SITE_URL = "http://a-zumi.net";

    /** 使用するフックポイント：使用するフックポイントを設定すると、フックポイントが競合した際にアラートが出ます。
     *  ここで宣言するとインストール時にdtb_plugin_hookpointsに登録され、
     *  register関数を書かずにフックポイントでの介入が可能です。
     */
    static $HOOK_POINTS = array(
        array("prefilterTransform", "prefilterTransform"), // コンパイル前のテンプレートをフック
    );

    /** ライセンス */
    static $LICENSE = "LGPL";

}
