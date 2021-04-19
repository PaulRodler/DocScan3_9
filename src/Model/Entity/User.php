<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $uid
 * @property string $uname
 * @property string $firstname
 * @property string $surname
 * @property string $gender
 * @property string $email
 * @property string $pwd
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'uname' => true,
        'firstname' => true,
        'surname' => true,
        'gender' => true,
        'email' => true,
        'pwd' => true,
    ];
}
