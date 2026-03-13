/**
 * NOTE: 
 * 2023.09.15 mbase-chat.jsの新設
 * 
 * /m_chatN/で必要な関数です。
 * WARNING: 読み込み後の自動スクロールは、./list2.htmlへの記載のみに変更しました。
 * 
 * 
 * NOTE: 
 * チャット画面をブラウザのウィンドウ内に正しく収めるため以下の変更が為されました。（全て部品の高さを判別する目的です）
 * 各種ID名に変更はありません。
 * 各種クラス名に変更はありません。（ただし、新しいクラス名の追記はあります。）
 * 
 * WARNING: headerタグに「data-header」属性が必要になりました。（HTMLの場所 header.html, headerN.html）
 * WARNING: .chat-outlineに「data-chat-outline」属性が必要になりました。（HTMLの場所 /m_chatN/list.html）
 * WARNING: .ifrmに「data-chat-iframe」属性が必要になりました。（HTMLの場所 /m_chatN/list.html）
 * WARNING: .data-chat-footerに「data-chat-footer」属性が必要になりました。（HTMLの場所 /m_chatN/list.html）
 * WARNING: .chat-headerが新設されました。チャットのヘッダー関連の部品は全てこの中に格納する必要があります。（HTMLの場所 /m_chatN/list.html）
 * WARNING: .chat-headerに「data-chat-header」属性が必要です。（HTMLの場所 /m_chatN/list.html）
**/


// Chat main
$(function () {
	// init
	setChatPartsHeight();
});
$(window).on('load resize',function(){
	// load resize
	setChatPartsHeight();
});

function setChatPartsHeight(){
	let $chat_iframe = $('[data-chat-iframe]');
	let chat = getChatPartsHeight();
	let iframeHeight = chat.window - ( chat.siteHeader + chat.bread + ( chat.outline - chat.header - chat.footer ) );
	
	// NOTE: チャットフレームの高さのみ調整
	$chat_iframe.removeAttr('style');
	$chat_iframe.height(iframeHeight);
}

function getChatPartsHeight(){
	let $header = $('[data-header]');
	let $bread = $('.breadcrumb');
	let $chat_outline = $('[data-chat-outline]');
	let $chat_header = $('[data-chat-header]');
	let $chat_iframe = $('[data-chat-iframe]');
	let $chat_footer = $('[data-chat-footer]');
	let obj = {
		window: window.innerHeight,
		siteHeader: $header.outerHeight(true),
		bread: $bread.outerHeight(true),
		outline: $chat_outline.outerHeight(true),
		header: $chat_header.outerHeight(true),
		iframe: $chat_iframe.outerHeight(true),// NOTE: デフォルト不使用
		footer: $chat_footer.outerHeight(true)
	}
	// NOTE: $element.outerHeight(true)でmarginも含んだ高さになります。
	return obj;
}

