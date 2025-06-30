<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
session_start();

// إعداد الاتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$password = "";
$dbName = "courses";

$conn = mysqli_connect($host, $user, $password, $dbName);
if (!$conn) {
    die("<div style='color:red;text-align:center;margin:20px;'>فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error() . "</div>");
}
// جلب أنواع المواد من قاعدة البيانات
$categoriesQuery = "SELECT DISTINCT category FROM course";
$categoriesResult = mysqli_query($conn, $categoriesQuery);
$categories = [];
if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0) {
    while ($row = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $row['category'];
    }
}

// إدارة الرسائل
$registerMessage = "";
$loginMessage = "";
$registrationSuccess = false;

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
    $username = trim($_POST['login_username']);
    $password = $_POST['login_password'];
    
    if (empty($username) || empty($password)) {
        $loginMessage = "<div class='error-message'>يرجى إدخال اسم المستخدم وكلمة المرور</div>";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT student_id, student_name, username, password FROM students WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['student_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['student_name'] = $user['student_name'];
                $_SESSION['logged_in'] = true;
                
                if (isset($_POST['remember_me'])) {
                    setcookie('remember_user', $user['student_id'], time() + (30 * 24 * 60 * 60), "/");
                }
                
                header("Location: dash.php");
                exit();
            } else {
                $loginMessage = "<div class='error-message'>كلمة المرور غير صحيحة</div>";
            }
        } else {
            $loginMessage = "<div class='error-message'>اسم المستخدم غير موجود</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// معالجة تسجيل جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
    $student_name = trim($_POST['student_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $username = trim($_POST['username']);
    $regster_date = date('Y-m-d H:i:s');

    if (empty($student_name) || empty($email) || empty($mobile) || empty($password) || empty($confirm_password) || empty($username)) {
        $registerMessage = "<div class='error-message'>يرجى تعبئة جميع الحقول.</div>";
    } elseif ($password !== $confirm_password) {
        $registerMessage = "<div class='error-message'>كلمتا المرور غير متطابقتين.</div>";
    } else {
        $check = mysqli_prepare($conn, "SELECT student_id FROM students WHERE email=? OR username=?");
        mysqli_stmt_bind_param($check, "ss", $email, $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $registerMessage = "<div class='error-message' id='email-username-error'>البريد الإلكتروني أو اسم المستخدم مستخدم بالفعل.</div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO students (student_name, email, mobile, password, regster_date, username) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("خطأ في إعداد الاستعلام: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt, "ssssss", $student_name, $email, $mobile, $hashed_password, $regster_date, $username);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['register_success'] = true;
                // $_SESSION['show_login_tab'] = true; // <--- تم إلغاء هذا السطر
                header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
                exit();
            } else {
                $registerMessage = "<div class='error-message'>حدث خطأ أثناء التسجيل: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
    mysqli_close($conn);
}

if (isset($_SESSION['register_success'])) {
    $registerMessage = "<div class='success-message' id='registration-success'>تم تسجيل الطالب بنجاح</div>";
    $registrationSuccess = true;
    unset($_SESSION['register_success']);
}

// تحديد التبويب النشط عند التحميل
$activeTab = ''; // لم نعد نحدد تبويبًا نشطًا افتراضيًا هنا بعد التسجيل
// if (isset($_SESSION['show_login_tab']) && $_SESSION['show_login_tab']) { // <--- تم إلغاء هذه الكتلة
//     $activeTab = 'login';
//     unset($_SESSION['show_login_tab']);
// }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجز كورسات دراسية أونلاين</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="شعار المؤسسة" class="logo">
        <h1 class="title">حجز كورسات دراسية أونلاين</h1>
    </div>
    
    <div class="main-container">
        <div class="right-section">
            <div class="tabs">
                <button class="tab <?php echo ($activeTab === 'login' ? 'active' : ''); ?>" data-tab="login">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول </button>
                <button class="tab <?php echo ($activeTab === 'register' ? 'active' : ''); ?>" data-tab="register">
                    <i class="fas fa-user-plus"></i> تسجيل جديد
                </button>
                <button class="tab" data-tab="courses">
                    <i class="fas fa-book"></i> الاقسام </button>
                <button class="tab" data-tab="about">
                    <i class="fas fa-info-circle"></i> من نحن</button>
            </div>
        </div>
        
        <div class="left-section">
            <div class="welcome-message" id="welcomeSection">
                <h2>مرحبًا بك في منصة SUPERKOURSE</h2>
                <p>منصة متخصصة في حجز الكورسات الدراسية عبر الإنترنت. اختر من بين مجموعة واسعة من الدورات في مختلف المجالات.</p>
                <p>لبدء استخدام المنصة، يرجى اختيار أحد الخيارات من القائمة على اليمين.</p>
                <button class="start-button">ابدأ رحلتك التعليمية</button>
            </div>
            
            <div id="courses-frame" class="hidden">
    <h2>تصفح الكورسات المتاحة</h2>
    
    <div class="course-category">
        <div class="course-title">لغات البرمجة</div>
        <select>
            <option value="">عرض</option>
            <option value="python">Python</option>
            <option value="java">Java</option>
            <option value="javascript">JavaScript</option>
            <option value="csharp">C#</option>
        </select>
    </div>
    
    <div class="course-category">
        <div class="course-title">قواعد البيانات</div>
        <select>
            <option value="">عرض</option>
            <option value="mysql">MySQL</option>
            <option value="oracle">Oracle</option>
            <option value="mongodb">MongoDB</option>
        </select>
    </div>
    
    <div class="course-category">
        <div class="course-title">لغات المحادثة</div>
        <select>
            <option value="">عرض</option>
            <option value="english">الإنجليزية</option>
            <option value="french">اللغة الفرنسية</option>
            <option value="german">الألمانية الأساسية</option>
            <option value="italian">الإيطالية</option>
        </select>
    </div>
</div>             
            <div class="form-container">
                <form id="registerForm" class="tab-content hidden" autocomplete="off" method="post" action="">
                    <h2>تسجيل طالب جديد</h2>
                    <input type="hidden" name="form_type" value="register">
                    <div id="register-message-area">
                        <?php if ($registerMessage && !$registrationSuccess) echo $registerMessage; ?>
                    </div>
                    <div class="form-group">
                        <label for="student_name">اسم الطالب الكامل</label>
                        <input type="text" id="student_name" name="student_name" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="mobile">رقم الجوال</label>
                        <input type="tel" id="mobile" name="mobile" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input type="password" id="password" name="password" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">تأكيد كلمة المرور</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="username">اسم المستخدم</label>
                        <input type="text" id="username" name="username" required autocomplete="off">
                    </div>
                    <button type="submit" class="btn">تسجيل حساب جديد</button>
                    <div id="registerMessage"></div>
                </form>

                <div id="success-message-area">
                    <?php if ($registrationSuccess) echo $registerMessage; ?>
                </div>

                <form id="loginForm" class="tab-content hidden" method="post" autocomplete="off">
                    <h2>تسجيل الدخول</h2>
                    <?php if (!empty($loginMessage)) echo $loginMessage; ?>
                    <input type="hidden" name="form_type" value="login">
                    <div class="form-group">
                        <label for="login_username">اسم المستخدم</label>
                        <input type="text" id="login_username" name="login_username" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="login_password">كلمة المرور</label>
                        <input type="password" id="login_password" name="login_password" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <input type="checkbox" id="remember_me" name="remember_me">
                        <label for="remember_me">تذكيري</label>
                    </div>
                    <a href="#" class="forgot-password">نسيت كلمة المرور؟</a>
                    <button type="submit" class="btn">تسجيل دخول</button>
                    <div class="or-separator"><span>أو</span></div>
                    <div class="register-link">
                        ليس لديك حساب؟ قم <a href="#" onclick="showTab('register');return false;">بالضغط هنا</a> لانشاء حساب جديد
                    </div>
                </form>
                
                <div id="aboutContent" class="tab-content hidden">
                    <h2>من نحن</h2>
                    <p>الغرض من منصة SUPERKOURSE :</p>
                    <ul>
                        <li>تقديم خدمات حجز كورسات دراسية عبر المنصة الالكترونية</li>
                        <li>موقع إلكتروني ومنصة رقمية للوساطة الإلكترونية في عرض محتوى التعليم الالكتروني</li>
                        <li>ربط الطالب بمقدم المحتوى</li>
                    </ul>
                    <div class="about-content">
                        <h3>رؤيتنا</h3>
                        <p>أن نكون المنصة الرائدة في تقديم خدمات التعليم الإلكتروني في العالم العربي، ونصل بالمعرفة إلى كل طالب عربي.</p>
                        
                        <h3>رسالتنا</h3>
                        <p>تقديم تجربة تعليمية متميزة عبر الإنترنت، تمكن الطلاب من الوصول إلى أفضل المحتويات التعليمية بسهولة ويسر.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        © 2025 منصة SUPERKOURSE - جميع الحقوق محفوظة
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const welcomeSection = document.getElementById('welcomeSection');
            const coursesFrame = document.getElementById('courses-frame');
            const formContainer = document.querySelector('.form-container');
            const successMessageArea = document.getElementById('success-message-area'); // الحصول على مرجع لمساحة الرسائل
            
            // إضافة حدث النقر لكل تبويب
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // إخفاء رسالة الترحيب
                    welcomeSection.style.display = 'none';
                    
                    // إزالة النشاط من كل التبويبات
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // إضافة النشاط للتبويب المحدد
                    tab.classList.add('active');
                    
                    // إظهار حاوية النماذج
                    formContainer.style.display = 'block';
                    
                    // إخفاء كل محتويات النماذج
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    const tabId = tab.getAttribute('data-tab');
                    
                    // إظهار المحتوى المناسب للتبويب
                    if (tabId === 'courses') {
                        coursesFrame.classList.remove('hidden');
                        coursesFrame.style.display = 'block';
                    } else {
                        coursesFrame.classList.add('hidden');
                        coursesFrame.style.display = 'none';
                        
                        // إظهار النموذج المناسب
                        document.getElementById(tabId + 'Form')?.classList.remove('hidden');
                        document.getElementById(tabId + 'Content')?.classList.remove('hidden');
                    }
                    
                    // إخفاء رسائل النجاح والأخطاء عند تغيير التبويب يدويا
                    successMessageArea.innerHTML = '';
                    document.getElementById('register-message-area').innerHTML = '';
                    document.getElementById('registerMessage').innerHTML = '';
                });
            });
            
            // زر البدء
            document.querySelector('.start-button').addEventListener('click', function() {
                // إظهار تبويب تسجيل الدخول (يبقى هذا السلوك لزر "ابدأ رحلتك التعليمية")
                const loginTab = document.querySelector('.tab[data-tab="login"]');
                loginTab.click();
            });
            
            // إذا كان هناك تبويب نشط من PHP (فقط في حالة الـ 'login' كخيار افتراضي سابق)
            // الآن تم إزالة show_login_tab لذا لن يتم تفعيل هذا إلا إذا كان $activeTab فارغًا
            const activeTabPHP = "<?php echo $activeTab; ?>"; // للحصول على القيمة النهائية من PHP

            if (activeTabPHP) { // إذا كان $activeTab مضبوطًا (وهو ما لن يحدث بعد التسجيل الناجح)
                const tabToActivate = document.querySelector(`.tab[data-tab="${activeTabPHP}"]`);
                if (tabToActivate) {
                    tabToActivate.click();
                }
            } else {
                // عند التحميل الأولي أو بعد التسجيل الناجح (حيث $activeTab فارغ)
                // تأكد من أن الأقسام مخفية وأن رسالة الترحيب ظاهرة
                formContainer.style.display = 'none';
                coursesFrame.style.display = 'none';
                welcomeSection.style.display = 'block';
            }
            
            // إخفاء رسالة النجاح بعد 3 ثواني
            var successMsg = document.getElementById('registration-success');
            if (successMsg) {
                // تأكد من ظهور حاوية النماذج ورسالة النجاح
                formContainer.style.display = 'block';
                welcomeSection.style.display = 'none'; // أخفِ قسم الترحيب
                
                setTimeout(function(){
                    successMessageArea.innerHTML = ''; // إخفاء الرسالة
                    // بعد إخفاء الرسالة، أعد عرض قسم الترحيب
                    welcomeSection.style.display = 'block';
                    formContainer.style.display = 'none'; // أخفِ حاوية النماذج
                }, 3000);
            }
            
            // إخفاء رسالة الخطأ بعد 3 ثواني
            var errorMsg = document.getElementById('email-username-error');
            if (errorMsg) {
                setTimeout(function(){
                    errorMsg.style.display = 'none';
                }, 3000);
            }
        });
        
        // دالة مساعدة لعرض تبويب معين
        function showTab(tabId) {
            const tab = document.querySelector(`.tab[data-tab="${tabId}"]`);
            if (tab) {
                // إزالة النشاط من كل التبويبات قبل التنشيط الجديد
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active'); // إضافة النشاط للتبويب المحدد

                // إخفاء جميع محتويات النماذج أولاً
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                    content.style.display = 'none'; // تأكد من إخفائها تمامًا
                });
                document.getElementById('courses-frame').classList.add('hidden');
                document.getElementById('courses-frame').style.display = 'none';

                // إظهار المحتوى المناسب
                const contentElement = document.getElementById(tabId + 'Form') || document.getElementById(tabId + 'Content');
                if (contentElement) {
                    contentElement.classList.remove('hidden');
                    contentElement.style.display = 'block';
                } else if (tabId === 'courses') {
                    document.getElementById('courses-frame').classList.remove('hidden');
                    document.getElementById('courses-frame').style.display = 'block';
                }
                // إخفاء رسائل النجاح والأخطاء عند التغيير اليدوي للتبويب
                document.getElementById('success-message-area').innerHTML = '';
                document.getElementById('register-message-area').innerHTML = '';
                document.getElementById('registerMessage').innerHTML = '';
            }
        }
        
        // تحقق فوري من كلمة المرور والتأكيد قبل الإرسال من جهة العميل
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                e.preventDefault();
                document.getElementById('registerMessage').innerHTML = '<div class="error-message">كلمة المرور وتأكيدها غير متطابقين</div>';
            }
        });
    </script>
</body>
</html>