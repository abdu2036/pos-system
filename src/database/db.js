const db = new Dexie('PosOfflineDB');

db.version(2).stores({
    invoices: '++id, uuid, invoice_number, total_amount, payment_method, is_synced, created_at',
    products: '++id, product_id, name, price, stock',
    customers: '++id, customer_id, name, phone'
});