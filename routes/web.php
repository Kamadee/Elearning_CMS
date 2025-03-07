<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\VideoController;
use App\Http\Middleware\PermissionMiddleware;


Route::group(['prefix' => 'auth', 'as' => 'auth'], function () {
  Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('.login'); // ok
  Route::post('/postLogin', [AuthController::class, 'postLogin'])->middleware('guest')->name('.postLogin'); // ok
  Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('.logout');
  Route::get('/detail', [AuthController::class, 'detail'])->middleware('auth')->name('.detail'); // ok
  Route::post('/updateProfile', [AuthController::class, 'updateProfile'])->middleware('auth')->name('.updateProfile'); // ok
});

// Quản lý khách hàng
Route::group(['prefix' => 'customer', 'as' => 'customer', 'middleware' => 'auth'], function () {
  Route::get('/list', [CustomerController::class, 'list'])->middleware(PermissionMiddleware::class . ':customer.list')->name('.list'); //ok
  Route::get('/anyData', [CustomerController::class, 'anyData'])->name('.anyData'); //ok
  Route::post('/update/{id}', [CustomerController::class, 'update'])->name('.updateCustomer'); //ok
  Route::get('/detail/{id}', [CustomerController::class, 'detail'])->middleware(PermissionMiddleware::class . ':customer.edit')->name('.detail'); //ok
});

// Quản lý admin (ok hết rồi)
Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => 'auth'], function () {
  Route::get('/list', [AdminController::class, 'list'])->middleware(PermissionMiddleware::class . ':admin.list')->name('.list');
  Route::get('/anyData', [AdminController::class, 'anyData'])->name('.anyData');
  Route::get('/create', [AdminController::class, 'createAccount'])->middleware(PermissionMiddleware::class . ':admin.create')->name('.createAccount');
  Route::post('/create', [AdminController::class, 'storeNewAccount'])->name('.createNewAccount');
  Route::post('/update/{id}', [AdminController::class, 'updateAccount'])->name('.updateAccount');
  Route::get('/detail/{id}', [AdminController::class, 'adminDetail'])->middleware(PermissionMiddleware::class . ':admin.edit')->name('.detail');
  Route::delete('/delete/{id}', [AdminController::class, 'deleteUser'])->name('.delete');
});

// Quản lý role (ok hết)
Route::group(['prefix' => 'role', 'as' => 'role', 'middleware' => 'auth'], function () {
  Route::get('/list', [AdminController::class, 'getRoleList'])->middleware(PermissionMiddleware::class . ':role.list')->name('.list');
  Route::get('/anyData', [AdminController::class, 'roleAnyData'])->name('.anyData');
  Route::get('/create', [AdminController::class, 'create'])->middleware(PermissionMiddleware::class . ':role.create')->name('.create');
  Route::post('/createRole', [AdminController::class, 'createRole'])->name('.createRole');
  Route::get('/detail/{id}', [AdminController::class, 'roleDetail'])->middleware(PermissionMiddleware::class . ':role.edit')->name('.detail');
  Route::post('/update/{id}', [AdminController::class, 'updateRole'])->name('.updateRole');
  Route::delete('/delete/{id}', [AdminController::class, 'deleteRole'])->name('.delete');
});

// Quản lý permisson 
Route::group(['prefix' => 'permission', 'as' => 'permission', 'middleware' => ['auth']], function () {
  Route::get('/list', [AdminController::class, 'getPermissionList'])->middleware('permission-check:permission.list')->name('.list');
  Route::get('/anyData', [AdminController::class, 'permissionAnyData'])->name('.anyData');
  Route::get('/create', [AdminController::class, 'createPermission'])->middleware('permission-check:permission.create')->name('.create');
  Route::post('/createPermission', [AdminController::class, 'storePermission'])->name('.createPermission');
  Route::get('/detail/{id}', [AdminController::class, 'permissionDetail'])->middleware('permission-check:permission.edit')->name('.detail');
  Route::post('/update/{id}', [AdminController::class, 'updatePermission'])->name('.updatePermission');
  Route::delete('/delete/{id}', [AdminController::class, 'deletePermission'])->name('.delete');
});

// Quản lý bài viết (còn xóa post và xóa ảnh thumbnail)
Route::group(['prefix' => 'posts', 'as' => 'posts', 'middleware' => 'auth'], function () {
  Route::get('/list', [PostController::class, 'list'])->middleware(PermissionMiddleware::class . ':post.list')->name('.list'); //ok
  Route::get('/create', [PostController::class, 'create'])->middleware(PermissionMiddleware::class . ':post.create')->name('.create'); //ok
  Route::post('/createPost', [PostController::class, 'createPost'])->name('.createPost'); //ok
  Route::get('/anyData', [PostController::class, 'anyData'])->name('.anyData'); //ok
  Route::post('/update/{id}', [PostController::class, 'updatePost'])->name('.update'); //ok
  Route::get('/detail/{id}', [PostController::class, 'detail'])->middleware(PermissionMiddleware::class . ':post.edit')->name('.detail'); //ok
  Route::post('/delete-img/{id}', [PostController::class, 'deleteThumbnai'])->name('.thumnail.delete');
  Route::post('/upload-img', [PostController::class, 'uploadImage'])->name('.uploadImage'); // ok
  Route::delete('/delete/{id}', [PostController::class, 'deletePost'])->middleware(PermissionMiddleware::class . ':post.delete')->name('.delete');
});

// Quản lý gắn thẻ tag (ok full)
Route::group(['prefix' => 'tag', 'as' => 'tag', 'middleware' => 'auth'], function () {
  Route::get('/list', [TagController::class, 'list'])->middleware(PermissionMiddleware::class . ':tag.list')->name('.list');
  Route::get('/anyData', [TagController::class, 'anyData'])->name('.anyData');
  Route::get('/create', [TagController::class, 'create'])->middleware(PermissionMiddleware::class . ':tag.create')->name('.create');
  Route::post('/update/{id}', [TagController::class, 'updateTag'])->name('.updateTag');
  Route::post('/createTag', [TagController::class, 'createTag'])->name('.createTag');
  Route::get('/detail/{id}', [TagController::class, 'detail'])->middleware(PermissionMiddleware::class . ':tag.edit')->name('.detail');
  Route::delete('/delete/{id}', [TagController::class, 'deleteTag'])->middleware(PermissionMiddleware::class . ':tag.delete')->name('.delete');
});

// Quản lý danh mục (ok full)
Route::group(['prefix' => 'category', 'as' => 'category', 'middleware' => 'auth'], function () {
  Route::get('/list', [CategoryController::class, 'list'])->middleware(PermissionMiddleware::class . ':category.list')->name('.list');
  Route::get('/anyData', [CategoryController::class, 'anyData'])->name('.anyData');
  Route::get('/create', [CategoryController::class, 'create'])->middleware(PermissionMiddleware::class . ':category.create')->name('.create');
  Route::post('/update/{id}', [CategoryController::class, 'updateCategory'])->name('.updateCategory');
  Route::post('/createTag', [CategoryController::class, 'createCategory'])->name('.createCategory');
  Route::get('/detail/{id}', [CategoryController::class, 'detail'])->middleware(PermissionMiddleware::class . ':category.edit')->name('.detail');
  Route::delete('/delete/{id}', [CategoryController::class, 'deleteCategory'])->middleware(PermissionMiddleware::class . ':category.delete')->name('.delete');
});
Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Quản lý banner
// Route::group(['prefix' => 'banner', 'as' => 'banner', 'middleware' => 'auth'], function () {
//   Route::get('/list', 'BannerController@list')->middleware('permission-check:event.list')->name('.list');
//   Route::get('/anyData', 'BannerController@anyData')->name('.anyData');
//   Route::get('/create', 'BannerController@create')->middleware('permission-check:banner.create')->name('.create');
//   Route::post('/update/{id}', 'BannerController@updateBanner')->name('.updateBanner');
//   Route::post('/createTag', 'BannerController@createBanner')->name('.createBanner');
//   Route::get('/detail/{id}', 'BannerController@detail')->middleware('permission-check:banner.edit')->name('.detail');
//   Route::post('/delete-img/{id}', 'BannerController@deleteImage')->name('.image.delete');
//   Route::delete('/delete/{id}', 'BannerController@deleteBanner')->name('.delete');
// });

// Quản lý video (ok hết, về sau bổ sung thêm xóa video là dc)
Route::group(['prefix' => 'video', 'as' => 'video', 'middleware' => 'auth'], function () {
  Route::get('/list', [VideoController::class, 'list'])->middleware(PermissionMiddleware::class . ':video.list')->name('.list');
  Route::get('/create', [VideoController::class, 'create'])->middleware(PermissionMiddleware::class . ':video.upload')->name('.create');
  Route::post('/uploadVideo', [VideoController::class, 'uploadVideo'])->name('.uploadVideo');
  Route::post('/saveVideoId', [VideoController::class, 'saveVideoId'])->name('.saveVideoId');
  Route::get('/anyData', [VideoController::class, 'anyData'])->name('.anyData');
  Route::get('/anyDataForCreate', [VideoController::class, 'anyDataForCreate'])->name('.anyDataForCreate');
  Route::delete('/delete/{id}', [VideoController::class, 'deleteVideo'])->middleware(PermissionMiddleware::class . ':video.delete')->name('.delete');
  Route::get('/vimeo/detail/{id}', [VideoController::class, 'vimeoDetail'])->name('.vimeoDetail');
  Route::get('/process', [VideoController::class, 'processUpload'])->name('.process');
  Route::get('/process/data', [VideoController::class, 'processData'])->name('.processData');
  Route::get('/vimeo/thumbnail', [VideoController::class, 'fetchVimeoThumbnail'])->name('.vimeoDetail');
});

// Quản lý khóa học (còn thêm video vào khóa học + xóa banner, thumbnail )
Route::group(['prefix' => 'courses', 'as' => 'courses', 'middleware' => 'auth'], function () {
  Route::get('/hot', [CourseController::class, 'hotCourse'])->middleware(PermissionMiddleware::class . ':course.list')->name('.hot');
  Route::post('/postHotCourse', [CourseController::class, 'postHotCourse'])->name('.postHotCourse');
  Route::get('/anyDataForHot', [CourseController::class, 'anyDataForHot'])->name('.anyDataForHot');
  Route::delete('/delete-hot/{id}', [CourseController::class, 'deleteHot'])->name('.delete-hot');
  Route::get('/list', [CourseController::class, 'list'])->middleware(PermissionMiddleware::class . ':course.list')->name('.list');
  Route::get('/create', [CourseController::class, 'create'])->middleware(PermissionMiddleware::class . ':course.create')->name('.create');
  Route::post('/createCourse', [CourseController::class, 'createCourse'])->name('.createCourse');
  Route::get('/anyData', [CourseController::class, 'anyData'])->name('.anyData');
  Route::post('/update/{id}', [CourseController::class, 'updateCourse'])->name('.update');
  Route::get('/detail/{id}', [CourseController::class, 'detail'])->middleware(PermissionMiddleware::class . ':course.edit')->name('.detail');
  Route::post('/delete-img/{id}', [CourseController::class, 'deleteThumbnai'])->name('.delete-thumnail');
  Route::post('/delete-img-banner/{id}', [CourseController::class, 'deleteBanner'])->name('.delete-banner');
  Route::post('/upload-img', [CourseController::class, 'uploadImage'])->name('.uploadImage');
  Route::delete('/delete/{id}', [CourseController::class, 'deleteCourse'])->name('.delete');
});
