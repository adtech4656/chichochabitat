<?php

namespace App\Support;

/**
 * The single registry of permissions in the system.
 *
 * Adding a permission here makes it appear in the admin matrix automatically.
 */
class Permissions
{
    /** Groups map to the checkbox matrix sections in the admin panel. */
    public const GROUPS = [
        'Properties' => 'Listings, verification and publication',
        'Agents' => 'KYC review and agent oversight',
        'Users' => 'Accounts, roles and access',
        'Content' => 'Site copy, pages and media',
        'System' => 'Integrations, keys and audit',
    ];

    public const ALL = [
        // Properties
        'properties.view' => ['Properties', 'View listings'],
        'properties.edit' => ['Properties', 'Edit listing details'],
        'properties.approve' => ['Properties', 'Approve & publish'],
        'properties.reject' => ['Properties', 'Reject / request info'],
        'properties.delete' => ['Properties', 'Delete listings'],

        // Agents
        'agents.view' => ['Agents', 'View agent profiles'],
        'agents.kyc' => ['Agents', 'Review agent KYC'],
        'agents.suspend' => ['Agents', 'Suspend an agent'],

        // Users
        'users.view' => ['Users', 'View accounts'],
        'users.manage' => ['Users', 'Create & edit accounts'],
        'users.permissions' => ['Users', 'Change roles & permissions'],
        'users.suspend' => ['Users', 'Suspend accounts'],

        // Content
        'content.view' => ['Content', 'View site content'],
        'content.edit' => ['Content', 'Edit site content & settings'],
        'pages.manage' => ['Content', 'Manage published pages'],

        // System
        'settings.view' => ['System', 'View integration settings'],
        'settings.edit' => ['System', 'Edit API keys & secrets'],
        'audit.view' => ['System', 'Read the audit log'],

        // Organization
        'branches.view' => ['Organization', 'View branches'],
        'branches.manage' => ['Organization', 'Create and edit branches'],
        'departments.view' => ['Organization', 'View departments'],
        'departments.manage' => ['Organization', 'Create and edit departments'],
        'staff.view' => ['Organization', 'View staff in assigned scope'],
        'staff.manage' => ['Organization', 'Edit staff profiles in assigned scope'],
        'staff.view.all' => ['Organization', 'View staff across all departments'],
        'manage_all_departments' => ['Organization', 'Manage staff across all departments'],
    ];

    /**
     * Role presets. Admin is implicit (holds every permission) and is
     * deliberately not listed here so it can never be partially revoked.
     */
    public const ROLE_PRESETS = [
        'sales_manager' => [
            'properties.view', 'properties.edit', 'properties.approve', 'properties.reject',
            'agents.view', 'content.view',
        ],
        'technical_manager' => [
            'properties.view', 'properties.reject', 'agents.view', 'agents.kyc', 'content.view',
        ],
        'legal' => [
            'properties.view', 'properties.reject', 'content.view', 'pages.manage',
        ],
        'general_manager' => [
            'users.view', 'branches.view', 'branches.manage', 'departments.view',
            'departments.manage', 'staff.view', 'staff.manage', 'staff.view.all',
            'manage_all_departments',
        ],
        'branch_manager' => [
            'branches.view', 'departments.view', 'staff.view', 'staff.manage',
        ],
        'department_head' => [
            'departments.view', 'staff.view', 'staff.manage',
        ],
        'agent' => [
            'properties.view', 'content.view',
        ],
        'customer' => [],
    ];

    public const ROLES = [
        'customer' => 'Customer',
        'agent' => 'Agent / Owner',
        'sales_manager' => 'Sales Manager',
        'technical_manager' => 'Technical Manager',
        'legal' => 'Legal Department',
        'general_manager' => 'General Manager',
        'branch_manager' => 'Branch Manager',
        'department_head' => 'Department Head',
        'admin' => 'Administrator',
    ];

    public static function rolePreset(string $role): array
    {
        return $role === 'admin'
            ? array_keys(self::ALL)
            : (self::ROLE_PRESETS[$role] ?? []);
    }
}
