<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Tag\TagRequest;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Tag\DeleteTagRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Role;
use App\Models\PermissionSetting;
use Illuminate\Support\Facades\Session;

class TagController extends BaseController
{
    /**
     * Function to add tags.
     *
     * @param TagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTag(TagRequest $request)
    {
        try {
            $requestParams = $request->all();
            if (!Gate::allows('superadmin')) {
                $requestParams['organizationId'] =  Auth::user()->organization_id;
            }
            $tags = Tag::addAdminTags($requestParams);
            if ($tags instanceof Tag) {
                $tagData = array(
                    'name' => $tags->name,
                    'id' => $tags->tag_id
                );
                return $this->successResponse(default_trans((Session::get('userOrganizationId').'/permission.success_messages.tag_add_success'), __('default/permission.success_messages.tag_add_success')), $tagData);
            } else{
                return $this->failResponse(default_trans((Session::get('userOrganizationId').'/permission.fail_messages.tag_add_fail'), __('default/permission.fail_messages.tag_add_fail')));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * Function to fetch tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTags(Request $request)
    {
        try {
            if (Gate::allows('superadmin')) {
                $requestParams = $request->all();
                $organizationId = $requestParams['organizationId'];
                $permissionId = $requestParams['permissionId'] ?? '';
            } else {
                $organizationId = Auth::user()->organization_id;
                $permissionId = $request->permissionId ?? '';
            }
            $userRoles = Role::userRole(Auth::user()->role_id, false)->get();
            $isFetched = $this->getTags($organizationId);
            
            //if ($isFetched == false) {
                //return $this->failResponse(__('message.tag_fetch_fail'));
            //} else {
            $tagSettings = PermissionSetting::getTagSettings($organizationId, $permissionId);
            $canAddTag = isset($tagSettings['tag_creation'][Auth::user()->role_id]) ? $tagSettings['tag_creation'][Auth::user()->role_id] : Gate::allows('superadmin') ? true : false;
            
            $view = view('permission.add-tag-popup', [
                'permissionId' => $permissionId, 'organizationSelectedId' =>  $organizationId,
                'userRoles' => $userRoles, 'tagSettings' => $tagSettings, 'canAddTag' => $canAddTag
            ])->render();
            return response()->json([
                'status' => true,
                'html'   => $view,
                'data' => $isFetched
            ]);
            //}
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
     /**
     * Function to delete tags.
     *
     * @param DeleteTagRequest $request
     * @throws Exception
     */
    public function deleteTag(DeleteTagRequest $request)
    {
        try {
            $requestParams = $request->validated();
            $tagId = $requestParams['tagId'] ?? 0;
            $tagId = decrypt($tagId);
            $organizationId = Tag::find($tagId)->organization_id;
            $isDeleted = Tag::deleteTags($tagId);
            if ($isDeleted == true) {
                $tags = $this->getTags($organizationId);
                return $this->successResponse(default_trans((Session::get('userOrganizationId').'/permission.success_messages.tag_delete_success'), __('default/permission.success_messages.tag_delete_success')), $tags);
            } else {
                return $this->failResponse(default_trans((Session::get('userOrganizationId').'/permission.fail_messages.tag_delete_fail'), __('default/permission.fail_messages.tag_delete_fail')));
            }
        } catch (\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
    
    /**
     * FUnction to get tags.
     *
     */
    private function getTags($organizationId)
    {
        try {
            $loggedInUser = Auth::id();

            $tags = Tag::getAdminTags($organizationId, $loggedInUser);

            $tags = $tags->makeHidden('id');
            if (!$tags->isEmpty()) {
                return $tags->toJson();
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
