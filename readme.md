# Hamazon

Contributors: Takahashi_Fumiki, hametuha  
Tags: amazon, affiliate, dmm, phg  
Requires at least: 4.7  
Tested up to: 4.9.8  
Requires PHP: 5.4  
Stable tag: 4.0.3

You can add affiliate link in post content via Amazon, iTunes, DMM.

##  Description

You can add affiliate link in visual editor button.
Search on editor and add it immediately.

### Available Services

* Amazon Advertising API
* PHG iTunes affiliate
* DMM affiliate

### Deprecated

These services below are deprecated.

* Rakuten
* Linkshare


##  Installation

1. Upload `wp-hamazon` folder in `/wp-content/plugins/` directory.
1. Activate plugin.
1. Go to `Setting > Affiliate Setting` and enter credentials.

##  Screenshots

1. You can search affiliate like this screen.

##  Changelog

### 4.0.3

* Change translation.

### 4.0.2

* Fix JS Error.

### 4.0.1

* Sorry! Fix non gutenberg environment.

### 4.0.0

* Fix PHG bug.
* Add Gutenberg support!

### 3.0.3

* Got [bug report](https://wordpress.org/support/topic/古いバージョンはどこにありますか？/#post-9600252) and remove typehint to avoid fatal error.

### 3.0.0

**BREAKING CHANGE!!**

* All codes are rewriten.
* Drop Rakuten and Linkshare. Shortcodes are now empty.
* Ready for [Shortcake](https://ja.wordpress.org/plugins/shortcode-ui/). Now you can preview shortcode resutl.
* If you feel this version breaks your site, stay old verison or contact me via [support forum]().

### 2.3.1

* 関数`tmkm_amazon_view`が動かなくなっていたので、直しました。
* HTMLリンクを出力する`hamazon_asin_link`を追加しました。

###  2.3

* ショートコード挿入ボタンをつけました。
* ショートコード内の情報を出力できるようにしました。一言コピーのようなものが入れられます。後方互換は取っているので、いままでのコンテンツはそのまま表示されます。
* サービスにPHG（iTuensアフィリエイト）とDMMを追加しました。

```
// これまで
[tmkm-amazon]00000000[/tmkm-amazon]
// これから
[tmkm-amazon asin='00000000']この本は最高ですよ！[/tmkm-amazon]
```

### 2.2

* 楽天をサービスとして追加
* マークアップをドラスティックに変更しました。これまで利用していた方は注意してください。

### 2.1

* リンクシェアのAPIがリクエストを返さないことがあるので、キャッシュ方法を変更

### 2.0

* リンクシェアを追加

### 1.0

* はじめてのリリース
