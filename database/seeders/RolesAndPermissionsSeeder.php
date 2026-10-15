<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تنظيف التخزين المؤقت للصلاحيات
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. قائمة الصلاحيات الشاملة
        $permissions = [
            // 🌐 إدارة لوحة التحكم والمسؤولين

            // 👑 إدارة الأدوار
            'manage roles', //إدارة الأدوار
            'view roles', //عرض الأدوار
            'create roles', //إنشاء الأدوار
            'edit roles', //تعديل الأدوار
            'delete roles', //حذف الأدوار    

            // 👤 إدارة المستخدمين
            'manage users', //المستخدمين
            'create users', //إنشاء مستخدمين
            'edit users', //تعديل مستخدمين
            'delete users', //حذف مستخدمين




            // 🏢 إدارة الشركة 
            'manage company',
            'view company',
            'create company',
            'edit company',


            // 🌍 إدارة  الفروع
            'manage branches', //إدارة الفروع
            'view branches',
            'create branches',
            'edit branches',
            'delete branches',


            // 👤 إدارة التصنفات
            'manage categories', //إدارة التصنفات
            'create categories',
            'edit categories',
            'delete categories',


            // 📦 إدارة المنتجات
            'manage products', //إدارة المنتجات
            'create products', //إضافة منتجات
            'edit products', //تعديل المنتجات
            'delete products', //حذف المنتجات
            'print products', //طاباعة المنتجات

            // 💰  ادارة فةتير الشراء 
            'manage purchase invoices', //إدارة فواتير الشراء
            'create purchase invoices', //إضافة فواتير الشراء
            'edit purchase invoices',  //تعديل فواتير الشراء
            'delete purchase invoices',  //حدف فواتير الشراء
            'print purchase invoices', //طاباعة فواتير الشراء
            'print supplier receipts', //طاباعة إيصالات الموردين
            'settle purchase invoices', //تسوية فواتير الشراء
            'print report invoices',   //طباعة تقرير فواتير الشراء


            //الزبائن
            'manage customers', //إدارة الزبائن
            'view customers', //عرض الزبائن
            'create customers', //إضافة الزبائن
            'edit customers', //تعديل الزبائن
            'delete customers', //حدف الزبائن
            'add customer payment', //إضافة سداد
            'show customers', //عرض تفاصيل الزبون
            'print customer receipt', //طباعة إيصال الزبون

            //الموردين
            'manage suppliers', //إدارة الموردين
            'view suppliers', //عرض الموردين
            'create suppliers', //إضافة الموردين
            'edit suppliers', //تعديل الموردين
            'delete suppliers', //حدف الموردين
            'add supplier payment', //إضافة سداد
            'show suppliers', //عرض تفاصيل المورد
            'print supplier receipt', //طباعة إيصال المورد

            //الفواتير
            'manage invoices', //إدارة الفواتير
            'view invoices', //عرض الفواتير
            'show invoices', //عرض تفاصيل الفاتورة
            'edit invoices', //تعديل الفواتير
            'delete invoices', //حدف الفواتير
            'print invoices', //طباعة الفواتير

            //الخزينة
            'manage treasuries', //إدارة الخزينة
            'view treasuries', //عرض الخزينة
            'create treasuries', //إضافة خزينة
            'edit treasuries', //تعديل الخزينة
            'delete treasuries', //حدف الخزينة
            'view reports', //عرض التقارير

            //الورديات
            'manage shifts', //إدارة الورديات   
            'open shifts', //فتح الورديات
            'close shifts', //إغلاق الورديات
            'edit shifts', //تعديل الورديات
            'show shifts', //عرض تفاصيل الورديات

        ];

        // 3. إنشاء الصلاحيات في قاعدة البيانات
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // 4. إنشاء الأدوار وتنسيق الصلاحيات

        // 👑 أ. المدير العام (Super Admin)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());
        // 5. إنشاء/ربط حساب الأدمن الرئيسي وإعطائه دور super-admin
        $user = User::firstOrCreate(
            ['email' => 'abdsalaam@ly'],
            [
                'name' => 'Abdsalaam',
                'password' => Hash::make('12345678'), // تغيير كلمة المرور عند الإنشاء الأول
            ]
        );

        $user->syncRoles([$superAdminRole->name]);
    }
}
