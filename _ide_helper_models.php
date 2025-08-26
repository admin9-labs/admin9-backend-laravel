<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject, \App\Models\Contracts\RoleAware {}
}

namespace App\Models{
/**
 * 
 *
 * @property string $id
 * @property string $name
 * @property string|null $mobile
 * @property string|null $email
 * @property string $password
 * @property string|null $nickname 用户昵称
 * @property string|null $introduction 个性签名
 * @property string|null $avatar
 * @property string|null $realname 真实姓名
 * @property string|null $idcard 身份证号
 * @property string|null $identity_verified_at 实名认证时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $email_verified
 * @property-read mixed $identity_verified
 * @property-read mixed $mobile_verified
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Workflow> $workflows
 * @property-read int|null $workflows_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdcard($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIdentityVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRealname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject, \App\Models\Contracts\RoleAware {}
}

namespace App\Models{
/**
 * 
 *
 * @property string $id
 * @property string $user_id 用户ID
 * @property string $name
 * @property string $content 工作流内容
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Workflow whereUserId($value)
 */
	class Workflow extends \Eloquent {}
}

