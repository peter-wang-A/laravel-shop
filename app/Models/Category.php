<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Category extends Model
{
    protected $filable = ['name', 'is_directory', 'level', 'path'];

    protected $cates = [
        'is_directory' => 'boolean'
    ];

    public static function boot()
    {
        // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
        parent::boot();
        static::creating(function (Category $category) {
            //如果创建一个根目录
            if (is_null($category->parent_id)) {
                //将层级设置为0，将根目录路径设置为 '-'
                $category->level = 0;
                $category->path = '-';
            } else {
                // 将层级设为父类目的层级 + 1
                $category->level = $category->parent->level + 1;
                // 将 path 值设为父类目的 path 追加父类目 ID 以及最后跟上一个 - 分隔符
                $category->path = $category->parent->path . $category->parent_id . '-';
            }
        });
    }

    //一个子分类只有一个父分类
    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    //一个父分类有多个子分类
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    //一个分类有多个商品
    public function products()
    {
        return $this->hasMany(Product::class);
    }


    //定义一个访问器，获取所有祖先类目的 ID 值
    public function getPathIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->path, '-')));
    }

    // 定义一个访问器，获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
        return Category::query()
            //调用上面的访问器，取得所有父类 ID
            ->whereIn('id', $this->path_ids)
            ->orderBy('level')
            ->get();
    }

    // 定义一个访问器，获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
    public function getItemNameAttribute()
    {
        return $this->ancestors
            ->pluck('name') //获取所有祖先类目的名称
            ->push($this->name) //将当前目录名称 push 到数组
            ->implode('-'); //用 '-' 将当前数组组装成一个字符串
    }
}
