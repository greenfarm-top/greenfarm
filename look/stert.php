
<?php
// --- هنا الكود بتاع Open Account ---
if (isset($_GET['users']) && isset($_GET['open'])) {
    if (filter_var($_GET['open'], FILTER_VALIDATE_INT) !== false) {
        $user_id = $_GET['open'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {			
             // ✅ نغير اسم الجلسة مؤقتًا لجلسة المستخدم
            $old_session = session_name(); // نخزن اسم الأدمن
          
            $_SESSION['user'] = [
                'id'               => $user['user_id'],
                'username'         => $user['username'],
                'email'            => $user['email'],
                'f_name'           => $user['f_name'],
                'user_agent'       => $user['user_agent'],
                'user_IP'          => $user['user_IP'],
                'save_IP'          => $user['save_IP'],
                'blocked'          => $user['blocked'],
                'record_time'      => $user['record_time'],
                'withdrawals_money'=> $user['withdrawals_money'],
                'payments_money'   => $user['payments_money'],
                'email_verified'   => $user['email_verified']
            ];

            

             session_regenerate_id(true); 

           

            // نفتح حساب اليوزر في تاب جديدة
            header("Location: /user");
            exit();
        }
    }}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
	    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f1f1f1;
        }

        /* شريط التنقل */
        .navbar {
            width: 100%;
            background: #333;
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            left: 0;
            height: 50px;
            z-index: 1000;
        }

        /* أيقونة القائمة */
        .menu-icon {
            font-size: 44px;
            cursor: pointer;
            background: none;
            border: none;
            color: white;
        }

        /* البحث في المنتصف */
        .search-container {
            flex-grow: 1;
            display: flex;
            justify-content: center;
        }

        .search-container input {
            width: 80%;
            max-width: 300px;
            padding: 8px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
        }

        .search-container button {
            padding: 8px 15px;
            border: none;
            background: #1877F2;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 5px;
        }

        /* القائمة الجانبية */
        .menu {
            width: 250px;
            background: #222;
            color: white;
            position: fixed;
            height: 100vh;
            left: -260px;
            top: 50px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }

        .menu.show {
            left: 0; z-index: 1000;
        }
  /* زر تسجيل الخروج في أسفل القائمة */
        #logout {
            background: red;
		padding: 12px;
		margin: 30px 3px 30px;
        }
        .menu button {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            background: #444;
            color: white;
            cursor: pointer;
            text-align: left;
            font-size: 18px;
            border-radius: 5px;
            transition: 0.2s;
        }

        .menu button:hover {
            background: #555;
        }

        .menu button.active {
            background: #1877F2;
            font-weight: bold;
        }

      

        /* محتوى الصفحة */
        .content {
			text-align: center;
            margin-left: 20px;
			padding: 5px 0px 10px;
			font-size: 20px;
			background: white;
			margin: 5px 0px 9px 0px;
			border-radius: 10px;
			box-shadow: 0px 0px 10px rgb(0 0 0 / 10%);
        }.content_title {
           
            font-size: 20px;
            background: white;
          
        }

        /* استجابة للأجهزة الصغيرة */
        @media (max-width: 760px) {
            .menu {
                width: 80%;
                max-width: 250px;
            }

            .search-container input {
                width: 60%;
            }
        }
    </style>

	
			<style>
			

		.stats-container {
			display: flex;
			justify-content: center;
			gap: 20px;
			padding: 15px;
			text-align: center;
		}

		.stats-box {
			background: #f8f9fa;
			border-radius: 8px;
			padding: 5px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
			width: 200px;
		}

		.stats-box h3 {
			margin: 5px 0;
			font-size: 20px;
			color: #333;
		}

		.stats-box p {
			font-size: 18px;
			font-weight: bold;
		}

		.payout {
			color: red;
		}

		.payment {
			color: blue;
		}

		.users {
			color: green;
		}
	
   @media (max-width: 768px) {
    .stats-container {
        flex-direction: column;
        align-items: center;
    }
}


    </style>

	
	</head>
<body>
<?php


// عدد المستخدمين
$result = $conn->query("SELECT COUNT(*) FROM users");
$user_count = $result->fetch_array()[0];


// مجموع مبالغ الإيداع
$result = $conn->query("SELECT SUM(advertising_balance) FROM user_money");
$total_payments = $result->fetch_array()[0] ?? 0;


// مجموع الرصيد المتاح للسحب لكل المستخدمين
$result = $conn->query("SELECT SUM(available_to_withdraw) FROM user_money");
$total_available_balance = $result->fetch_array()[0] ?? 0;


// مجموع الهدايا خلال آخر 24 ساعة
$result = $conn->query("SELECT COUNT(*) FROM daily_gifts WHERE created_at >= NOW() - INTERVAL 1 DAY");
$gifts_last_24h = $result->fetch_array()[0] ?? 6;

// عدد المستخدمين الأونلاين
$result = $conn->query("SELECT COUNT(*) FROM user_activity WHERE is_online = 1");
$online_users = $result->fetch_array()[0];

// 🔴 تحقق من طلبات السحب المعلقة
$result = $conn->query("SELECT COUNT(*) FROM withdrawals WHERE withdrawal_status = 'pending'");
$pending_withdrawals = $result->fetch_array()[0] ?? 0;

// 🟢 تحقق من طلبات الإيداع المعلقة
$result = $conn->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'");
$pending_payments = $result->fetch_array()[0] ?? 0;

?>

    <!-- شريط التنقل -->
    <div class="navbar">
        <button class="menu-icon" onclick="toggleMenu();">☰</button>
        <div class="search-container">
            <input type="text" id="search-input" placeholder="🔍payeer ابحث هنا...">
            <button onclick="search()">بحث</button>
        </div>
    </div>
<br><br><br>
    <!-- القائمة الجانبية -->
    <div class="menu" id="menu">
		<button onclick="window.location.href = '?home';">🏠 الصفحة الرئيسية</button>
		 <button onclick="window.location.href = '?users';">👥 المستخدمين</button>
		 <button onclick="window.location.href = '?withdrawRequests';">💰 طلبات السحب</button>
           <button onclick="window.location.href = '?depositRequests';">💵 طلبات الإيداع</button>
		
      
       
     
        <button onclick="showPage('ads', this);window.location.href = '?ads';">📢 الإعلانات</button>
        <button onclick="showPage('adViews', this);window.location.href = '?adViews';">👀 مشاهدات الإعلانات</button>
        
        <button id="logout" onclick="logout()">🚪 تسجيل الخروج</button>
    </div>
	<script> 
	function toggleMenu() {
            const menu = document.getElementById("menu");
            menu.classList.toggle("show");
        }
	</script>	
	
	<style>
.blink {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    animation: blink 1s infinite;
    margin-top: 10px;
}
.red { background-color: red; }
.green { background-color: green; }

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}
</style>
<div class="stats-container">

    <div class="stats-box users">
        <h3>عدد المستخدمين</h3>
        <p><?php echo number_format($user_count); ?></p>
    </div>

    <div class="stats-box payment">
        <h3>رصيد متاح للشراء</h3>
        <p>$<?php echo number_format($total_payments, 2); ?></p>
        <?php if ($pending_payments > 0): ?>
            <div class="blink green" title="طلبات إيداع معلقة"></div>
        <?php endif; ?>
    </div> 

    <div class="stats-box payment">
        <h3>الرصيد المتاح للسحب</h3>
        <p>$<?php echo number_format($total_available_balance, 2); ?></p>
        <?php if ($pending_withdrawals > 0): ?>
            <div class="blink red" title="طلبات سحب معلقة"></div>
        <?php endif; ?>
    </div> 

    <div class="stats-box users">
        <h3>عدد الهدايا</h3>
        <p><?php echo $gifts_last_24h; ?></p>
    </div>

    <div onclick="window.location.href = '?home';" class="stats-box users">
        <h3>المستخدمين المتصلين</h3>
        <p><?php echo number_format($online_users); ?></p>
    </div>

</div>

 

  <?php
	
		if (isset($_GET['home'])){// تسجيل الخروج
			include("home.php");
		
		
		}elseif (isset($_GET['users'])){
			include("users.php");
		}elseif (isset($_GET['withdrawRequests'])){
			include("withdrawRequests.php");
		}elseif (isset($_GET['depositRequests'])){
			include("depositRequests.php");
		
		}elseif (isset($_GET['ads'])){
			include("ads.php");
		
		}elseif (isset($_GET['adViews'])){
			include("adViews.php");
		
		}
	?>
	
  
  
 
	

</body>
</html>
