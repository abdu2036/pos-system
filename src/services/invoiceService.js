import { db } from '../database/db'; // تأكد من مسار استيراد ملف db.js الصحيح حسب مشروعك

export async function saveInvoiceLocally(invoiceData) {
    try {
        // توليد معرف فريد UUID محلياً إذا لم يكن موجوداً
        const uuid = crypto.randomUUID();

        const localInvoice = {
            uuid: uuid,
            invoice_number: invoiceData.invoice_number || 'INV-' + Date.now(),
            branch_id: invoiceData.branch_id || 1,
            customer_id: invoiceData.customer_id || null,
            shift_id: invoiceData.shift_id || 1,
            total_amount: invoiceData.total_amount,
            paid_amount: invoiceData.paid_amount || invoiceData.total_amount,
            change_amount: invoiceData.change_amount || 0,
            payment_method: invoiceData.payment_method || 'cash',
            items: invoiceData.items || [],
            is_synced: false, // مؤشر أن الفاتورة لم تُرفع للسيرفر بعد
            created_at: new Date().toISOString()
        };

        // إدخال الفاتورة إلى جدول invoices في IndexedDB
        await db.invoices.add(localInvoice);

        console.log('تم حفظ الفاتورة محلياً بنجاح (Offline Mode) 📦');
        return { success: true, uuid };

    } catch (error) {
        console.error('فشل حفظ الفاتورة محلياً:', error);
        return { success: false, error: error.message };
    }
}