<?php
require_once __DIR__ . '/../includes/customer_auth.php';
customer_logout();
header('Location: ?p=home&lang=' . $lang);
exit;
