<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h4>صابونيتي</h4>
                <a href="#">قصتنا</a>
                <a href="#">المكونات الطبيعية</a>
                <a href="#">الاستدامة</a>
                <a href="#">مدونة العناية</a>
            </div>
            <div class="footer-column">
                <h4>التسوق</h4>
                <a href="shop.php">جميع المنتجات</a>
                <a href="#">صابون طبيعي</a>
                <a href="#">شامبو طبيعي</a>
                <a href="#">عطور فاخرة</a>
                <a href="#">مجموعات الهدايا</a>
            </div>
            <div class="footer-column">
                <h4>خدمة العملاء</h4>
                <a href="#">الشحن والإرجاع</a>
                <a href="#">الأسئلة الشائعة</a>
                <a href="#">سياسة الخصوصية</a>
                <a href="#">شروط الاستخدام</a>
                <a href="#">تواصل معنا</a>
            </div>
            <div class="footer-column">
                <h4>تواصل معنا</h4>
                <div class="contact-info">
                    <a href="https://wa.me/201094622639" target="_blank" class="whatsapp-contact">
                        <i class="fab fa-whatsapp"></i>
                        <span>واتساب: +20 10 94622639</span>
                    </a>
                </div>
                <div class="social-links">
                    <h4 style="margin-top: 20px;">تابعنا</h4>
                    <a href="https://www.facebook.com/share/1b9NbikqLq/" target="_blank" class="social-link facebook">
                        <i class="fab fa-facebook-f"></i>
                        <span>فيسبوك</span>
                    </a>
                    <a href="#" target="_blank" class="social-link instagram">
                        <i class="fab fa-instagram"></i>
                        <span>انستقرام</span>
                    </a>
                    <a href="https://wa.me/201094622639" target="_blank" class="social-link whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        <span>واتساب</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>© 2025 صابونيتي. جميع الحقوق محفوظة</p>
            <p class="heart">🧼 صنع بعناية من مكونات طبيعية 100%</p>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: #1a1a1a;
        color: #fff;
        padding: 60px 0 30px;
        margin-top: 80px;
        font-family: 'Inter', sans-serif;
    }
    
    .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 40px;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 40px;
        margin-bottom: 50px;
    }
    
    .footer-column h4 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: #c6a43b;
        position: relative;
        display: inline-block;
    }
    
    .footer-column h4::after {
        content: '';
        position: absolute;
        bottom: -8px;
        right: 0;
        width: 40px;
        height: 2px;
        background: #c6a43b;
    }
    
    .footer-column a {
        display: block;
        color: #aaa;
        text-decoration: none;
        font-size: 0.85rem;
        margin-bottom: 12px;
        transition: all 0.3s;
    }
    
    .footer-column a:hover {
        color: #c6a43b;
        transform: translateX(-5px);
    }
    
    /* معلومات الاتصال */
    .contact-info {
        margin-bottom: 20px;
    }
    
    .whatsapp-contact {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #25d366;
        color: white !important;
        padding: 12px 15px;
        border-radius: 50px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    
    .whatsapp-contact i {
        font-size: 1.3rem;
    }
    
    .whatsapp-contact:hover {
        background: #128c7e;
        transform: translateX(-5px);
        color: white !important;
    }
    
    /* روابط التواصل الاجتماعي */
    .social-links {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 10px;
    }
    
    .social-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        background: rgba(255,255,255,0.05);
        border-radius: 30px;
        transition: all 0.3s;
    }
    
    .social-link i {
        width: 24px;
        font-size: 1.1rem;
        text-align: center;
    }
    
    .social-link span {
        font-size: 0.85rem;
    }
    
    .social-link.facebook:hover {
        background: #1877f2;
        color: white;
        transform: translateX(-5px);
    }
    
    .social-link.instagram:hover {
        background: linear-gradient(45deg, #f09433, #d62976, #962fbf);
        color: white;
        transform: translateX(-5px);
    }
    
    .social-link.whatsapp:hover {
        background: #25d366;
        color: white;
        transform: translateX(-5px);
    }
    
    .copyright {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid #333;
        font-size: 0.8rem;
        color: #888;
    }
    
    .copyright .heart {
        margin-top: 10px;
        font-size: 0.75rem;
        color: #c6a43b;
    }
    
    @media (max-width: 992px) {
        .footer-content {
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 0 20px;
        }
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .footer-column h4::after {
            right: 50%;
            transform: translateX(50%);
        }
        .whatsapp-contact {
            justify-content: center;
        }
        .social-links {
            align-items: center;
        }
        .social-link {
            width: 200px;
            justify-content: center;
        }
        .footer-column a:hover {
            transform: translateX(0);
        }
    }
    
    @media (max-width: 480px) {
        .footer {
            padding: 40px 0 20px;
        }
        .social-link {
            width: 100%;
        }
    }
</style>