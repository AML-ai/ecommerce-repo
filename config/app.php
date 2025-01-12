<?php
// إعدادات AWS Cognito الخاصة بحساب المستخدم، المفتاح السري، والمنطقة
define('AWS_ACCESS_KEY', 'AKIA5FCD576WYRIMGT4B');
define('AWS_SECRET_KEY', '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');
define('AWS_REGION', 'eu-north-1'); // منطقة AWS الخاصة بالخدمات

// إعدادات AWS Cognito الخاصة بمجمع المستخدمين وعميل التطبيق
define('COGNITO_USER_POOL_ID', 'eu-north-1_8uCw4DcAs'); // معرف تجمع المستخدمين في Cognito
define('COGNITO_APP_CLIENT_ID', '7rh1fus3m7se7fhifevlasjn9a'); // معرف تطبيق Cognito

// إعدادات خدمة S3 الخاصة بالتخزين
define('S3_BUCKET', 'ecommerce-assetsbucket'); // اسم الدلو (Bucket) في S3
?>
