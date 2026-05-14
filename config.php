<?php

// 環境設定

// MYSQLの暗号化鍵
define('DB_ENC_KEY', 'C5DEEC2665C6FDE92824546D6A130F1887122EA35F175FC396E8C030B5B2678A');

// DB
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'under_sqluser');
define('DB_PASSWD', 'under4618');
define('DB_DBNAME', 'under_sql');

// Domain
define('BASE_DOMAIN', 'under-jp.com');
define('BASE_URL', 'https://' . BASE_DOMAIN);

// Mail
define('SENDER_EMAIL', 'info@ms123.jp');
define('SENDER_NAME', 'WEBSITE-NAME');

// Website Name
define('WEBSITE_NAME', 'WEBSITE-NAME');

// Company Name
define('COMPANY_NAME', 'COMPANY-NAME');

// Account Caption
define('M1_CAPTION', '不動産会社');
define('M2_CAPTION', '投資ユーザー');
define('O1_CAPTION', '不動産情報');
define('O2_CAPTION', 'ユーザー希望条件');

// Upload Check
define('UPLOADABLE', 'jpg,jpeg,png,gif,bmp,pdf,xls,doc,xlsx,docx,ppt,ps,eps,psd,ai,mov,mp3,wav,mov,mp4,mpg,avi,wmv,txt,csv,zip,lzh,tar,gz');

// メッセージステータス
define('STATUS_LIST', '依頼中::依頼受領::完了報告::依頼終了::依頼取消（M2理由）::中止（M1理由）');

// メッセージのやりとりが始まった直後のメッセージステータス
define('FIRST_STATUS', 'メッセージ中');

//（１）M1なのかM2なのか
//（２）どのステータスと時か（初期の場合はデフォルトと入力）
//（３）ボタン名（ボタンがない場合はなし）
//（４）テキスト名（テキストがない場合はなし）
//（５）ボタン押下時のステータス（次のステータス）
define('STATUS_ARRAY', array(
    'M1::なし::なし::現在依頼待ちの状態です::なし',
    'M2::なし::依頼する::なし::依頼中',
    'M1::依頼中::依頼受領::なし::依頼受領',
    'M1::依頼中::依頼を取り消す::なし::依頼中止',
    'M2::依頼中::なし::ご依頼の連絡をしました。::なし',
    'M2::依頼中::依頼をキャンセル::なし::依頼取消（M2理由）',
    'M1::依頼受領::完了報告::なし::完了報告',
    'M1::依頼受領::依頼を取消::なし::中止（M1理由）',
    'M2::依頼受領::なし::依頼が受領されました。::なし',
    'M1::完了報告::なし::お客様に完了報告を送りました。::なし',
    'M2::完了報告::依頼終了::なし::依頼終了',
    'M1::依頼終了::評価する::なし::なし',
    'M2::依頼終了::評価する::なし::なし', 
    'M1::依頼取消（M2理由）::なし::依頼取消しました。::なし',
    'M2::依頼取消（M2理由）::なし::依頼取消しました。::なし',
    'M1::中止（M1理由）::なし::中止しました。::なし',
    'M2::中止（M1理由）::なし::中止しました。::なし'
));
define('LAST_STATUS', '依頼終了');
