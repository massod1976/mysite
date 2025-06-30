<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header("Location: test211.php");
    exit();
}

// استرجاع بيانات المستخدم من الجلسة
$username = $_SESSION['username'] ?? 'ضيف';
$student_name = $_SESSION['student_name'] ?? '';
$student_id = $_SESSION['user_id'];

// جلب بيانات الطالب من الجلسة
require_once 'db_connection.php';

try {
    $stmt = $conn->prepare("SELECT
        student_name AS name,
        email,
        mobile AS phone,
        regster_date AS registration_date
    FROM students
    WHERE student_id = ?");

    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $student_data = $result->fetch_assoc();

} catch (Exception $e) {
    // معالجة الخطأ بشكل مناسب
    $student_data = [];
    error_log("Student data fetch error: " . $e->getMessage());
}

$student_info = [
    'name' => $student_data['name'] ?? $student_name,
    'email' => $student_data['email'] ?? 'غير متوفر',
    'phone' => $student_data['phone'] ?? 'غير متوفر',
    'registration_date' => $student_data['registration_date'] ?? 'غير محدد'
];
// ===== بداية الكود المضاف ===== //
// جلب الكورسات المحجوزة للطالب
$enrolled_courses = [];
try {
    $stmt = $conn->prepare("
        SELECT
            r.reservation_id,
            r.reservation_date,
            r.status AS reservation_status,
            c.course_id,
            c.course_name,
            c.category,
            c.description,
            c.price
        FROM reservation r
        JOIN course c ON r.course_id = c.course_id
        WHERE r.student_id = ?
        ORDER BY r.reservation_date DESC
    ");

    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $enrolled_courses[] = [
                'course_id' => $row['course_id'],
                'course_name' => $row['course_name'],
                'category' => $row['category'],
                'reservation_date' => $row['reservation_date'],
                'reservation_status' => $row['reservation_status'],
                'price' => $row['price'],
                'description' => $row['description']
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching enrolled courses: " . $e->getMessage());
}
// ===== نهاية الكود المضاف ===== //
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجز كورسات دراسية أونلاين</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styledash.css">
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="SUPER KOURSE Academy Logo" class="logo">
        <h1 class="title">حجز كورسات دراسية أونلاين</h1>
    </div>

    <div class="main-container">
        <div class="right-section">
            <div class="tabs">
                <span class="tab">
                    اسم المستخدم: <br>
                    <span id="userDisplayName"><?php echo htmlspecialchars($username); ?></span>
                </span>
                <button class="tab" data-tab="Explor"> تصفح الكورسات</button>
                <button class="tab" data-tab="courses"> حجز كورسات</button>
                <button class="tab" data-tab="about">عن المنصة</button>
                <button class="tab" id="logoutBtn">تسجيل الخروج</button>
            </div>
        </div>

        <div class="left-section">
            <div class="top-tabs">
                <button class="top-tab active" id="home-tab">الرئيسية</button>
                <button class="top-tab" id="account-tab">حسابي</button>
            </div>

            <div id="account-subtabs" class="account-subtabs hidden">
                <button class="account-subtab active" data-content="profile-content">ملفي</button>
                <button class="account-subtab" data-content="mycourses-content">كورساتي</button>
            </div>

            <div id="welcome-content" class="content-wrapper">
                <div class="content-sections">
                    <h2>مرحبًا بك في منصة حجز الكورسات، <?php echo htmlspecialchars($username); ?></h2>
                    <p class="intro-text">هنا يمكنك إدارة كورساتك التعليمية في أكاديمية SUPER KOURSE</p>

                    <div class="simple-links-container">
                        <ul class="simple-links">
                            <li class="browse">
                                <a href="#" class="link-item" onclick="showTab('Explor'); return false;">
                                    <span class="link-icon">🔍</span>
                                    <span class="link-text">
                                        <span class="link-title">تصفح الكورسات</span>
                                        <span class="link-description">اختر من مجموعتنا الواسعة من الكورسات</span>
                                    </span>
                                </a>
                            </li>

                            <li class="booking">
                                <a href="#" class="link-item" onclick="showTab('courses'); return false;">
                                    <span class="link-icon">📅</span>
                                    <span class="link-text">
                                        <span class="link-title">حجز كورسات</span>
                                        <span class="link-description">احجز كورساتك المفضلة لبدء التعلم</span>
                                    </span>
                                </a>
                            </li>

                            <li class="mycourses">
                                <a href="#" class="link-item" onclick="showTab('account-tab'); document.querySelector('.account-subtab[data-content=\"mycourses-content\"]').click(); return false;">
                                    <span class="link-icon">🎓</span>
                                    <span class="link-text">
                                        <span class="link-title">كورساتي</span>
                                        <span class="link-description">راجع كورساتك المسجلة وتابع تقدمك</span>
                                    </span>
                                </a>
                            </li>

                            <li class="profile">
                                <a href="#" class="link-item" onclick="showTab('account-tab'); document.querySelector('.account-subtab[data-content=\"profile-content\"]').click(); return false;">
                                    <span class="link-icon">👤</span>
                                    <span class="link-text">
                                        <span class="link-title">ملفي الشخصي</span>
                                        <span class="link-description">قم بتحديث بيانات حسابك وإعداداتك</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <p class="">
                        يمكنك تصفح الكورسات أو التنقل مباشرة إلى أحد الأقسام أعلاه.
                    </p>
                </div>
            </div>

            <div id="courses-Explor" class="content-wrapper hidden">
                <h2>تصفح الكورسات المتاحة</h2>
                <div id="explorNormalView">
                    <div class="course-category">
                        <div class="course-title">لغات البرمجة</div>
                        <select class="explor-course-select" data-category="programming">
                            <option value="">عرض</option>
                            <option value="Python">Python</option>
                            <option value="Java">Java</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="C#">C#</option>
                        </select>
                    </div>

                    <div class="course-category">
                        <div class="course-title">قواعد البيانات</div>
                        <select class="explor-course-select" data-category="database">
                            <option value="">عرض</option>
                            <option value="MySQL">MySQL</option>
                            <option value="Oracle">Oracle</option>
                            <option value="MongoDB">MongoDB</option>
                        </select>
                    </div>

                    <div class="course-category">
                        <div class="course-title">لغات المحادثة</div>
                        <select class="explor-course-select" data-category="language">
                            <option value="">عرض</option>
                            <option value="english">الإنجليزية</option>
                            <option value="french">اللغة الفرنسية</option>
                            <option value="german">الألمانية الأساسية</option>
                            <option value="italian">الإيطالية</option>
                        </select>
                    </div>
                </div>
                <div id="explorSelectedView" class="hidden">
                    <div class="course-details-card">
                        <h2 id="courseDetailName" style="text-align: center;"></h2>
                        <div class="course-meta">
                            <div><strong>نوع الكورس:</strong> <span id="courseDetailType"></span></div>
                            <div><strong>المستويات:</strong> مبتدئ، متوسط، متقدم</div>
                            <div><strong>الأسعار:</strong> <span id="courseDetailPrice">$99</span></div>
                        </div>
                        <div class="course-description">
                            <h3>وصف الكورس:</h3>
                            <p id="courseDetailDesc">وصف مفصل للكورس سيظهر هنا...</p>
                        </div>
                        <div class="action-buttons" style="margin-top: 20px;">
                            <button id="backToExplor" class="btn btn-secondary">رجوع</button>
                        </div>
                    </div>
                </div>
            </div>


            <div id="courses-frame" class="content-wrapper hidden">
                <h2 style="text-align: center;">احجز كورس</h2>
                <div id="normalView">
                    <div class="course-category">
                        <div class="course-title">لغات البرمجة</div>
                        <select class="course-select" data-category="programming">
                            <option value="">اختر لغة برمجة</option>
                            <option value="Python">Python</option>
                            <option value="Java">Java</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="PHP">PHP</option>
                        </select>
                    </div>

                    <div class="course-category">
                        <div class="course-title">قواعد البيانات</div>
                        <select class="course-select" data-category="database">
                            <option value="">اختر نظام قاعدة بيانات</option>
                            <option value="MySQL">MySQL</option>
                            <option value="Oracle">Oracle</option>
                            <option value="MongoDB">MongoDB</option>
                        </select>
                    </div>

                    <div class="course-category">
                        <div class="course-title">لغات المحادثة</div>
                        <select class="course-select" data-category="language">
                            <option value="">اختر لغة محادثة</option>
                            <option value="English">English</option>
                            <option value="French">French</option>
                            <option value="German">German</option>
                            <option value="Italian">Italian</option> 
                        </select>
                    </div>
                </div>

                <div id="selectedCourseView" class="hidden">
                    <div class="selected-course-card">
                        <h3 id="selectedCourseName"></h3>
                        <p id="selectedCourseCategory"></p>
                        <div class="action-buttons">
                            <button id="confirmBooking" class="btn btn-primary">تأكيد الحجز</button>
                            <button id="cancelSelection" class="btn btn-secondary">رجوع</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="aboutContent" class="content-wrapper hidden">
                <h2>عن المنصة</h2>
                <p>الغرض من منصة SUPERKOURSE :</p>
                <ul>
                    <li>تقديم خدمات حجز كورسات دراسية عبر المنصة الالكترونية</li>
                    <li>موقع إلكتروني ومنصة رقمية للوساطة الإلكترونية في عرض محتوى التعليم الالكتروني</li>
                    <li>ربط الطالب بمقدم المحتوى</li>
                </ul>
            </div>

            <div id="profile-content" class="content-wrapper hidden">
                <h2>ملفي الشخصي</h2>
                <div class="form-group">
    <label for="studentIdDisplay">رقم الطالب:</label>
    <input type="text" id="studentIdDisplay" value="<?php echo htmlspecialchars($student_id); ?>" readonly>
</div>                <div class="form-group">
                    <label for="studentName">الاسم الكامل:</label>
                    <input type="text" id="studentName" value="<?php echo htmlspecialchars($student_info['name']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="studentEmail">البريد الإلكتروني:</label>
                    <input type="text" id="studentEmail" value="<?php echo htmlspecialchars($student_info['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="studentPhone">رقم الهاتف:</label>
                    <input type="text" id="studentPhone" value="<?php echo htmlspecialchars($student_info['phone']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="registrationDate">تاريخ التسجيل:</label>
                    <input type="text" id="registrationDate" value="<?php echo htmlspecialchars($student_info['registration_date']); ?>" readonly>
                </div>
                <button class="btn-primary" style="width: auto;">تغيير كلمة المرور</button>
            </div>

            <div id="mycourses-content" class="content-wrapper hidden">
                <h2>كورساتي المسجلة</h2>

                <?php if (!empty($enrolled_courses)): ?>
                    <div class="enrolled-courses-grid">
                        <?php foreach ($enrolled_courses as $course): ?>
                            <div class="enrolled-course-card">
                                <div class="course-header">
                                    <h3 class="course-title"><?= htmlspecialchars($course['course_name']) ?></h3>
                                    <span class="course-category"><?= htmlspecialchars($course['category']) ?></span>
                                </div>

                                <div class="course-details">
                                    <p><strong>تاريخ الحجز:</strong> <?= htmlspecialchars($course['reservation_date']) ?></p>
                                    <p><strong>الحالة:</strong>
                                        <span class="status-badge <?= $course['reservation_status'] === 'نشط' ? 'active' : 'completed' ?>">
                                            <?= htmlspecialchars($course['reservation_status']) ?>
                                        </span>
                                    </p>
                                    <p><strong>السعر:</strong> <?= htmlspecialchars($course['price']) ?> ر.س</p>
                                </div>

                                <div class="course-description">
                                    <?= htmlspecialchars($course['description']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-courses">
                        <img src="empty-courses.png" alt="لا توجد كورسات" class="empty-icon">
                        <p>لم تسجل في أي كورسات بعد.</p>
                        <button class="btn-primary" onclick="showCoursesTab()">تصفح الكورسات المتاحة</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // عناصر التبويبات والمحتوى
            const homeTab = document.getElementById('home-tab');
            const accountTab = document.getElementById('account-tab');
            const accountSubtabs = document.getElementById('account-subtabs');
            const welcomeContent = document.getElementById('welcome-content');
            const profileContent = document.getElementById('profile-content');
            const mycoursesContent = document.getElementById('mycourses-content');
            const sideTabs = document.querySelectorAll('.tab[data-tab]');
            const allContentWrappers = document.querySelectorAll('.content-wrapper');
            const coursesExplorFrame = document.getElementById('courses-Explor'); // Added this line
            // عرض المحتوى الافتراضي
            function showDefaultContent() {
                allContentWrappers.forEach(wrapper => wrapper.classList.add('hidden'));
                welcomeContent.classList.remove('hidden');
                sideTabs.forEach(t => t.classList.remove('active'));
                homeTab.classList.add('active');
                accountTab.classList.remove('active');
                accountSubtabs.classList.add('hidden');
            }
            showDefaultContent();
            // تبويب الرئيسية
            homeTab.addEventListener('click', function() {
                showDefaultContent();
            });
           // تبويب حسابي
            accountTab.addEventListener('click', function() {
                allContentWrappers.forEach(wrapper => wrapper.classList.add('hidden'));
                homeTab.classList.remove('active');
                accountTab.classList.add('active');
                accountSubtabs.classList.remove('hidden');
                profileContent.classList.remove('hidden');
                mycoursesContent.classList.add('hidden');
                sideTabs.forEach(t => t.classList.remove('active'));

                // تحديد تبويب "ملفي" افتراضياً
                document.querySelectorAll('.account-subtab').forEach(t => t.classList.remove('active'));
                document.querySelector('.account-subtab[data-content="profile-content"]').classList.add('active');
                // طباعة معرف الطالب (من الجلسة PHP إلى JavaScript)
                const studentId = '<?php echo $student_id; ?>';
                console.log("Student ID:", studentId);

            });

            // تبويبات حسابي الفرعية
            document.querySelectorAll('.account-subtab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.account-subtab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    allContentWrappers.forEach(wrapper => wrapper.classList.add('hidden'));
                    const contentId = this.getAttribute('data-content');
                    document.getElementById(contentId).classList.remove('hidden');
                });
            });

            // تبويبات القسم الأيمن
            sideTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    allContentWrappers.forEach(wrapper => wrapper.classList.add('hidden'));
                    sideTabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    homeTab.classList.remove('active');
                    accountTab.classList.remove('active');
                    accountSubtabs.classList.add('hidden');

                    const tabId = tab.getAttribute('data-tab');
                    if (tabId === 'Explor') {
                        coursesExplorFrame.classList.remove('hidden'); // Use the specific ID for Explor tab
                    } else if (tabId === 'courses') {
                        document.getElementById('courses-frame').classList.remove('hidden');
                    } else if (tabId === 'about') {
                        document.getElementById('aboutContent').classList.remove('hidden');
                    }
                });
            });

            // باقي كود JavaScript (عرض الكورسات، الحجز، إلخ) يبقى كما هو
            const explorSelects = document.querySelectorAll('.explor-course-select');
            const explorNormalView = document.getElementById('explorNormalView');
            const explorSelectedView = document.getElementById('explorSelectedView');
            const backToExplorBtn = document.getElementById('backToExplor');

            // بيانات الكورسات (يجب أن تكون هذه البيانات متوفرة بشكل ثابت أو من مصدر آخر)
            const coursesData = {
                python: {
                    name: "Python",
                    type: "برمجة",
                    price: "$99",
                    desc: "تعلم أساسيات البرمجة باستخدام Python من الصفر حتى الاحتراف."
                },
                java: {
                    name: "Java",
                    type: "برمجة",
                    price: "$120",
                    desc: "دورة شاملة لتطوير تطبيقات Java للمبتدئين والمتقدمين."
                },
                javascript: {
                    name: "JavaScript",
                    type: "برمجة",
                    price: "$110",
                    desc: "أساسيات JavaScript وتطبيقاتها في تطوير الويب."
                },
                csharp: {
                    name: "C#",
                    type: "برمجة",
                    price: "$130",
                    desc: "تعلم لغة C# لبناء تطبيقات سطح المكتب والويب باستخدام .NET."
                },
                mysql: {
                    name: "MySQL",
                    type: "قواعد بيانات",
                    price: "$85",
                    desc: "مقدمة لقواعد البيانات العلائقية وإدارة MySQL."
                },
                oracle: {
                    name: "Oracle",
                    type: "قواعد بيانات",
                    price: "$150",
                    desc: "تعلم Oracle Database والتطوير باستخدام SQL و PL/SQL."
                },
                mongodb: {
                    name: "MongoDB",
                    type: "قواعد بيانات",
                    price: "$95",
                    desc: "التعرف على قواعد بيانات NoSQL والعمل مع MongoDB."
                },
                english: {
                    name: "الإنجليزية",
                    type: "لغة محادثة",
                    price: "$70",
                    desc: "تحسين مهارات المحادثة والاستماع باللغة الإنجليزية."
                },
                french: {
                    name: "اللغة الفرنسية",
                    type: "لغة محادثة",
                    price: "$80",
                    desc: "مبادئ اللغة الفرنسية للمبتدئين."
                },
                german: {
                    name: "الألمانية الأساسية",
                    type: "لغة محادثة",
                    price: "$75",
                    desc: "أساسيات اللغة الألمانية للمسافرين والمبتدئين."
                },
                italian: {
                    name: "الإيطالية",
                    type: "لغة محادثة",
                    price: "$65",
                    desc: "دورة مكثفة لتعلم اللغة الإيطالية."
                }
            };


            // عند اختيار كورس للعرض
            explorSelects.forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value) {
                        explorNormalView.classList.add('hidden');
                        const course = coursesData[this.value] || {
                            name: this.options[this.selectedIndex].text,
                            type: this.getAttribute('data-category') === 'programming' ? 'برمجة' :
                                    this.getAttribute('data-category') === 'database' ? 'قواعد بيانات' : 'لغة محادثة',
                            price: "$--",
                            desc: "لا يوجد وصف متاح حالياً لهذا الكورس."
                        };

                        document.getElementById('courseDetailName').textContent = course.name;
                        document.getElementById('courseDetailType').textContent = course.type;
                        document.getElementById('courseDetailPrice').textContent = course.price;
                        document.getElementById('courseDetailDesc').textContent = course.desc;

                        explorSelectedView.classList.remove('hidden');
                    }
                });
            });

            // زر الرجوع
            backToExplorBtn.addEventListener('click', function() {
                explorSelects.forEach(select => select.value = '');
                explorSelectedView.classList.add('hidden');
                explorNormalView.classList.remove('hidden');
            });

            // كود حجز الكورسات (يبقى كما هو لأنه يستخدم نفس الهيكل الثابت)
            const courseSelects = document.querySelectorAll('.course-select');
            const normalView = document.getElementById('normalView');
            const selectedCourseView = document.getElementById('selectedCourseView');
            const selectedCourseName = document.getElementById('selectedCourseName');
            const selectedCourseCategory = document.getElementById('selectedCourseCategory');
            const cancelSelectionBtn = document.getElementById('cancelSelection');
            const confirmBookingBtn = document.getElementById('confirmBooking');

            courseSelects.forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value) {
                        normalView.classList.add('hidden');
                        const categoryName = this.getAttribute('data-category');
                        const courseName = this.options[this.selectedIndex].text;
                        selectedCourseName.textContent = courseName;
                        selectedCourseCategory.textContent = "نوع الكورس: " +
                            (categoryName === 'programming' ? 'برمجة' :
                                categoryName === 'database' ? 'قواعد بيانات' : 'لغة محادثة');

                        selectedCourseView.classList.remove('hidden');
                    }
                });
            });

            cancelSelectionBtn.addEventListener('click', function() {
                courseSelects.forEach(select => select.value = '');
                selectedCourseView.classList.add('hidden');
                normalView.classList.remove('hidden');
            });

            // ----------------------------------------------------------------------------------
            // بداية الجزء المُعدّل/المدمج لـ confirmBookingBtn.addEventListener
            // ----------------------------------------------------------------------------------
            confirmBookingBtn.addEventListener('click', function() {
                const courseName = document.getElementById('selectedCourseName').textContent;
                const studentId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : "غير متاح"; ?>';

                // Corrected console.log to use 'courseName'
                console.log('Course Name before sending:', courseName);
                console.log('Student ID before sending:', studentId); // It's good to log this too for debugging

                fetch('get_course_id.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `course_name=${encodeURIComponent(courseName)}`,
                })
                .then(response => {
                    if (!response.ok) {
                        // If the HTTP status is not 2xx, throw an error
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // --- This is the missing part for handling the JSON response ---
                    if (data.success) {
                        const courseId = data.course_id;
                        // Now you have courseId and studentId, you can proceed to book the course
                        console.log('Received Course ID:', courseId);

                        // Example: Make another fetch call to reservation.php
                        fetch('reservation.php', { // Assuming reservation.php handles the actual booking
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `student_id=${encodeURIComponent(studentId)}&course_id=${encodeURIComponent(courseId)}`,
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(bookingData => {
                            if (bookingData.success) {
                                alert(`تم حجز الكورس بنجاح: ${bookingData.message}`);
                                // Add logic to update UI after successful booking
                                // For example, hide selected course view, show normal view
                                selectedCourseView.classList.add('hidden'); // إخفاء عرض الكورس المحدد
                                normalView.classList.remove('hidden');    // إظهار العرض الطبيعي
                                courseSelects.forEach(select => select.value = ''); // مسح اختيار الكورس
                                // يمكنك هنا إضافة تحديث لقسم "كورساتي" إذا لزم الأمر
                                // (مثلاً، إعادة تحميل البيانات أو إضافة الكورس الجديد ديناميكيًا)
                            } else {
                                alert(`فشل حجز الكورس: ${bookingData.message}`);
                            }
                        })
                        .catch(bookingError => {
                            console.error('Error during booking reservation:', bookingError);
                            alert('حدث خطأ أثناء تسجيل الحجز. يرجى المحاولة مرة أخرى.');
                        });

                    } else {
                        alert(`فشل الحصول على معلومات الكورس: ${data.message}`);
                    }
                })
                .catch(error => {
                    // --- This is the missing part for handling fetch errors ---
                    console.error('Error during course ID retrieval:', error);
                    alert('حدث خطأ أثناء جلب معرف الكورس. يرجى المحاولة مرة أخرى.');
                });
            });
            // ----------------------------------------------------------------------------------
            // نهاية الجزء المُعدّل/المدمج لـ confirmBookingBtn.addEventListener
            // ----------------------------------------------------------------------------------

            // تسجيل الخروج
            document.getElementById('logoutBtn').addEventListener('click', function() {
                window.location.href = 'logout.php';
            });

             // Helper function to show tabs from simple links
            window.showTab = function(tabName) {
                if (tabName === 'Explor') {
                    document.querySelector('.tab[data-tab="Explor"]').click();
                } else if (tabName === 'courses') {
                    document.querySelector('.tab[data-tab="courses"]').click();
                } else if (tabName === 'account-tab') {
                    document.getElementById('account-tab').click();
                }
            };
            window.showCoursesTab = function() {
                document.querySelector('.tab[data-tab="courses"]').click();
            };
        });
    </script>
</body>
</html>