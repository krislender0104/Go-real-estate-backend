<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            
            $table->increments('id');
            // $table->string('name')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_id')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('activated')->default(false);
            $table->boolean('newsletter')->default(true);
            $table->string('token');
            // $table->ipAddress('signup_ip_address')->nullable();
            // $table->ipAddress('signup_confirmation_ip_address')->nullable();
            // $table->ipAddress('signup_sm_ip_address')->nullable();
            // $table->ipAddress('admin_ip_address')->nullable();
            // $table->ipAddress('updated_ip_address')->nullable();
            // $table->ipAddress('deleted_ip_address')->nullable();

            // $table->string('socket_token', 255)->nullable();
            // $table->integer('last_chat_id')->unsigned()->nullable();
            // $table->boolean('online_state')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
