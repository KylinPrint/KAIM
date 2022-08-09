<?php

use App\Models\Brand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_name_unique');
            $table->dropUnique('brands_name_en_unique');

            $table->unique(['name','name_en'],'brands_name_zh_en_unique');
        });

        $brandNameNullArr = Brand::where('name',null)->orWhere('name_en',null)->get();
        foreach($brandNameNullArr as $brandNameNull){
            if($brandNameNull->name = null){
                $brandNameNull->name = '';
            }
            elseif($brandNameNull->name_en = null){
                $brandNameNull->name = '';
            }
            $brandNameNull->save();
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->nullable()->comment('品牌名称')->default('')->change();
            $table->string('name_en')->nullable()->comment('品牌英文名')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->nullable(false)->comment('品牌名称')->change();
            $table->string('name_en')->nullable(false)->comment('品牌英文名')->change();
        });

        $brandNameBlankArr = Brand::where('name','')->orWhere('name_en','')->get();
        foreach($brandNameBlankArr as $brandNameBlank){
            if($brandNameBlank->name = ''){
                $brandNameBlank->name = null;
            }
            elseif($brandNameBlank->name_en = ''){
                $brandNameBlank->name = null;
            }
            $brandNameBlank->save();
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_name_zh_en_unique');

            $table->uniqid('name','brands_name_unique');
            $table->uniqid('name_en','brands_name_en_unique');

        });
    }
};
