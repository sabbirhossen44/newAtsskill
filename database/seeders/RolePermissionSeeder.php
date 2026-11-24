<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * 105 permissions exactly as in the old dump
     * (guard `admin`, same names and group_name values).
     * Super Admin gets all permissions; Manager gets the 64
     * permissions it held in `role_has_permissions`.
     * Requires spatie/laravel-permission and an Admin model
     * using the HasRoles trait (guard `admin`).
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => 'dashboard.view', 'guard_name' => 'admin', 'group_name' => 'dashboard'],
            ['name' => 'admin.profile.view', 'guard_name' => 'admin', 'group_name' => 'admin profile'],
            ['name' => 'admin.profile.update', 'guard_name' => 'admin', 'group_name' => 'admin profile'],
            ['name' => 'admin.view', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'admin.create', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'admin.store', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'admin.edit', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'admin.update', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'admin.delete', 'guard_name' => 'admin', 'group_name' => 'admin'],
            ['name' => 'blog.category.view', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.create', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.translate', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.store', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.edit', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.update', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.category.delete', 'guard_name' => 'admin', 'group_name' => 'blog category'],
            ['name' => 'blog.view', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.create', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.translate', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.store', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.edit', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.update', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.delete', 'guard_name' => 'admin', 'group_name' => 'blog'],
            ['name' => 'blog.comment.view', 'guard_name' => 'admin', 'group_name' => 'blog comment'],
            ['name' => 'blog.comment.update', 'guard_name' => 'admin', 'group_name' => 'blog comment'],
            ['name' => 'blog.comment.delete', 'guard_name' => 'admin', 'group_name' => 'blog comment'],
            ['name' => 'role.view', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.create', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.store', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.assign', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.edit', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.update', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'role.delete', 'guard_name' => 'admin', 'group_name' => 'role'],
            ['name' => 'setting.view', 'guard_name' => 'admin', 'group_name' => 'setting'],
            ['name' => 'setting.update', 'guard_name' => 'admin', 'group_name' => 'setting'],
            ['name' => 'basic.payment.view', 'guard_name' => 'admin', 'group_name' => 'basic payment'],
            ['name' => 'basic.payment.update', 'guard_name' => 'admin', 'group_name' => 'basic payment'],
            ['name' => 'contect.message.view', 'guard_name' => 'admin', 'group_name' => 'contect message'],
            ['name' => 'contect.message.delete', 'guard_name' => 'admin', 'group_name' => 'contect message'],
            ['name' => 'currency.view', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'currency.create', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'currency.store', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'currency.edit', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'currency.update', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'currency.delete', 'guard_name' => 'admin', 'group_name' => 'currency'],
            ['name' => 'customer.view', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.bulk.mail', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.create', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.store', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.edit', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.update', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'customer.delete', 'guard_name' => 'admin', 'group_name' => 'customer'],
            ['name' => 'language.view', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.create', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.store', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.edit', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.update', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.delete', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.translate', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'language.single.translate', 'guard_name' => 'admin', 'group_name' => 'language'],
            ['name' => 'menu.view', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'menu.create', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'menu.store', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'menu.edit', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'menu.update', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'menu.delete', 'guard_name' => 'admin', 'group_name' => 'menu builder'],
            ['name' => 'page.management', 'guard_name' => 'admin', 'group_name' => 'page builder'],
            ['name' => 'payment.view', 'guard_name' => 'admin', 'group_name' => 'payment'],
            ['name' => 'payment.update', 'guard_name' => 'admin', 'group_name' => 'payment'],
            ['name' => 'newsletter.view', 'guard_name' => 'admin', 'group_name' => 'newsletter'],
            ['name' => 'newsletter.mail', 'guard_name' => 'admin', 'group_name' => 'newsletter'],
            ['name' => 'newsletter.delete', 'guard_name' => 'admin', 'group_name' => 'newsletter'],
            ['name' => 'testimonial.view', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.create', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.translate', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.store', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.edit', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.update', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'testimonial.delete', 'guard_name' => 'admin', 'group_name' => 'testimonial'],
            ['name' => 'faq.view', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.create', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.translate', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.store', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.edit', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.update', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'faq.delete', 'guard_name' => 'admin', 'group_name' => 'faq'],
            ['name' => 'location.view', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'location.create', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'location.store', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'location.edit', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'location.update', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'location.delete', 'guard_name' => 'admin', 'group_name' => 'locations'],
            ['name' => 'instructor.request.list', 'guard_name' => 'admin', 'group_name' => 'instructor request'],
            ['name' => 'instructor.request.setting', 'guard_name' => 'admin', 'group_name' => 'instructor request'],
            ['name' => 'course.management', 'guard_name' => 'admin', 'group_name' => 'courses'],
            ['name' => 'course.certificate.management', 'guard_name' => 'admin', 'group_name' => 'course certificate management'],
            ['name' => 'badge.management', 'guard_name' => 'admin', 'group_name' => 'Badges'],
            ['name' => 'order.management', 'guard_name' => 'admin', 'group_name' => 'order management'],
            ['name' => 'coupon.management', 'guard_name' => 'admin', 'group_name' => 'coupon management'],
            ['name' => 'withdraw.management', 'guard_name' => 'admin', 'group_name' => 'withdraw management'],
            ['name' => 'appearance.management', 'guard_name' => 'admin', 'group_name' => 'site appearance management'],
            ['name' => 'section.management', 'guard_name' => 'admin', 'group_name' => 'site appearance management'],
            ['name' => 'brand.management', 'guard_name' => 'admin', 'group_name' => 'brand management'],
            ['name' => 'footer.management', 'guard_name' => 'admin', 'group_name' => 'footer management'],
            ['name' => 'social.link.management', 'guard_name' => 'admin', 'group_name' => 'social link management'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'],
                 'guard_name' => $permission['guard_name']],
                ['group_name' => $permission['group_name']]
            );
        }

        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'admin']
        );
        $superAdmin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(
            ['name' => 'Manager', 'guard_name' => 'admin']
        );
        $manager->syncPermissions([
            'dashboard.view',
            'blog.category.view',
            'blog.category.create',
            'blog.category.translate',
            'blog.category.store',
            'blog.category.edit',
            'blog.category.update',
            'blog.category.delete',
            'blog.view',
            'blog.create',
            'blog.translate',
            'blog.store',
            'blog.edit',
            'blog.update',
            'blog.delete',
            'role.view',
            'role.edit',
            'role.update',
            'contect.message.view',
            'contect.message.delete',
            'currency.view',
            'currency.create',
            'currency.store',
            'currency.edit',
            'currency.update',
            'currency.delete',
            'customer.view',
            'customer.bulk.mail',
            'customer.create',
            'customer.store',
            'customer.edit',
            'customer.update',
            'customer.delete',
            'page.management',
            'payment.view',
            'payment.update',
            'newsletter.view',
            'newsletter.mail',
            'newsletter.delete',
            'testimonial.view',
            'testimonial.create',
            'testimonial.translate',
            'testimonial.store',
            'testimonial.edit',
            'testimonial.update',
            'testimonial.delete',
            'faq.view',
            'faq.create',
            'faq.translate',
            'faq.store',
            'faq.edit',
            'faq.update',
            'faq.delete',
            'location.view',
            'location.create',
            'location.store',
            'location.edit',
            'location.update',
            'location.delete',
            'course.management',
            'course.certificate.management',
            'badge.management',
            'coupon.management',
            'brand.management',
        ]);
    }
}
