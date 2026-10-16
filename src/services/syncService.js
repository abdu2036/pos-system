import { db } from '../database/db';

export async function syncOfflineInvoices() {
    // 1. التحقق مما إذا كان الإنترنت متصلاً
    if (!navigator.onLine) {
        console.log('لا يوجد اتصال بالإنترنت حالياً. المزامنة مؤجلة.');
        return;
    }

    try {
        // 2. جلب الفواتير غير المزامنة فقط من IndexedDB
        const unsyncedInvoices = await db.invoices
            .where('is_synced')
            .equals(false)
            .toArray();

        if (unsyncedInvoices.length === 0) {
            console.log('لا توجد فواتير معلقة للمزامنة.');
            return;
        }

        console.log(`جاري مزامنة ${unsyncedInvoices.length} فاتورة مع الخادم...`);

        // 3. إرسال البيانات إلى الـ API الخاص بـ Laravel
        const response = await fetch('http://127.0.0.1:8000/api/v1/sync/invoices', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer 2|py5h7oqZZK08R2Zq4Ssxi23TQyVxXr8AqKk3fwhd6657f33c' // استبدل التوكن بـ Token المستخدم الفعلي عند تسجيل الدخول
            },
            body: JSON.stringify({
                invoices: unsyncedInvoices
            })
        });

        const result = await response.json();

        if (response.ok && result.status) {
            // 4. تحديث حالة الفواتير محلياً لتصبح تمت مزامنتها
            for (const invoice of unsyncedInvoices) {
                await db.invoices
                    .where('uuid')
                    .equals(invoice.uuid)
                    .modify({ 
                        is_synced: true, 
                        synced_at: new Date().toISOString() 
                    });
            }
            console.log('تمت مزامنة الفواتير المحلية بنجاح وتحديث قاعدة البيانات المحلية! 🚀');
        } else {
            console.error('فشلت استجابة السيرفر أثناء المزامنة:', result);
        }

    } catch (error) {
        console.error('حدث خطأ أثناء الاتصال بالسيرفر للمزامنة:', error);
    }
}

// 5. الاستماع التلقائي لعودة الإنترنت لتشغيل المزامنة فوراً
window.addEventListener('online', () => {
    console.log('عادت شبكة الإنترنت! بدء المزامنة التلقائية...');
    syncOfflineInvoices();
});

// أو التحقق دورياً كل دقيقة كاحتياط
setInterval(syncOfflineInvoices, 60000);


async function syncMasterDataDown() {
    if (!navigator.onLine) {
        console.log('الجهاز أوفلاين. سيتم الاعتماد على البيانات المخزنة محلياً.');
        return;
    }

    try {
        console.log('جاري تحديث بيانات المنتجات والعملاء محلياً...');
        
        let response = await fetch('/api/v1/pos/initial-data', { // أو المسار المخصص لجلب المنتجات والعملاء
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer 2|py5h7oqZZK08R2Zq4Ssxi23TQyVxXr8AqKk3fwhd6657f33c'
            }
        });

        let data = await response.json();

        if (response.ok && data.success) {
            // تفريغ الجداول القديمة وإعادة تعبئتها بالبيانات المحدثة من السيرفر
            await db.products.clear();
            if (data.products && data.products.length > 0) {
                await db.products.bulkAdd(data.products);
            }

            await db.customers.clear();
            if (data.customers && data.customers.length > 0) {
                await db.customers.bulkAdd(data.customers);
            }

            console.log('تم تحديث قاعدة البيانات المحلية (المنتجات والعملاء) بنجاح! 📂');
        }
    } catch (error) {
        console.warn('تعذر جلب البيانات الأساسية من السيرفر، يتم استخدام النسخة المحلية.', error);
    }
}

// تشغيل الدالة تلقائياً عند تحميل الصفحة إذا كان هناك إنترنت
window.addEventListener('DOMContentLoaded', () => {
    syncMasterDataDown();
});