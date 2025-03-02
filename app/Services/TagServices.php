<?php

namespace App\Services;

use App\Models\Tag;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;

class TagServices
{
  public function getTags()
  {
    $queries = Tag::withCount('posts')->get();
    return $queries;
  }
  public function formatTagDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('tag.edit')) {
          $action .= '<a href="/tag/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('tag.update_tag') . '</a>';
        }
        if (Helper::checkPermission('tag.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->tag_name . '" class="btn-delete-tag btn btn-danger btn-sm">' . __('tag.delete_tag') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function processCreateTag($formData)
  {
    try {
      $tagData = [
        'tag_name' => $formData['tag_name'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];
      // dd($postData);
      DB::beginTransaction();
      $tagId = Tag::insertGetId($tagData);

      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
        'id' => $tagId
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdateTag($id, $formData)
  {
    try {
      $tagData = [
        'tag_name' =>  $formData['tag_name'],
      ];
      DB::beginTransaction();
      Tag::where('id', $id)->update($tagData);
      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteTag($tagId)
  {
    try {
      DB::beginTransaction();
      $tag = Tag::find($tagId);
      $tag->posts()->detach();
      $tag->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }
}
