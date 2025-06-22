<?php

namespace App\Models;

/**
 * User model
 */
class User extends Model
{
    public int $id;
    public string $username;
    public string $password;
    public string $role = 'employee';
    public string $created_at;
}