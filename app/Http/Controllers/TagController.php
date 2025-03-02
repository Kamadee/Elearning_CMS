<?php

namespace App\Http\Controllers;

use App\Services\TagServices;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TagController extends Controller
{
  private $tagServices;

  public function __construct(TagServices $tagServices)
  {
    $this->tagServices = $tagServices;
  }

  public function list()
  {
    return view('tag.index');
  }

  public function anyData(Request $request)
  {
    $data = $this->tagServices->getTags();
    $dataTagTables = $this->tagServices->formatTagDatatables($data);
    return $dataTagTables;
  }

  public function detail(Request $request)
  {
    $tag = Tag::find($request->id);
    return view('tag.detail', [
      'tag' => $tag,
    ]);
  }

  public function create()
  {
    return view('tag.create');
  }

  public function createTag(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'tag_name' => 'required|max:255',
    ]);
    if ($validator->fails()) {
      return redirect()->route('tag.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->tagServices->processCreateTag($request->all());
    if ($result['status']) {
      $tagId = $result['id'];
      return redirect()->route('tag.detail', ['id' => $tagId])
        ->withSuccess(__('tag.message.create_tag_success'));
    }
    return redirect()->route('tag.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function updateTag(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:tags,id',
      'tag_name' => 'required|max:255',
    ]);
    if ($validator->fails()) {
      return redirect()->route('tag.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->tagServices->processUpdateTag($request->id, $request->all());
    // dd($result);
    if ($result['status']) {
      return redirect()->route('tag.detail', ['id' => $request->id])
        ->withSuccess(__('tag.message.update_tag_success', ['id' => $request->id]));
    }
    return redirect()->route('tag.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deleteTag(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:tags,id',
    ]);
    if ($validator->fails()) {
      return [
        'status' => false,
        'message' => __('tag.message.tag_not_found')
      ];
    }
    $tagId = $request->id;
    return $this->tagServices->processDeleteTag($tagId);
  }
}
