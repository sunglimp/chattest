<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Group;
use App\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PermissionSetting;
use Illuminate\Database\Eloquent\Collection;
use function GuzzleHttp\json_decode;

class Organization extends Model
{

    const  ACTIVE =1;
    use SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_DEACTIVE = 0;

    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'company_name','contact_name','mobile_number','email','website','logo',
        'seat_alloted','timezone', 'surbo_unique_key','status', 'languages','validity_date', 'is_testing'
    ];

//    protected $appends = ['user_ids'];

    public function getAccessTokenAttribute()
    {
        return $this->surbo_unique_key;
    }

    public static function updateOrganization($request)
    {

        $organization = Organization::find($request['organization_id']);
        $organization->company_name = $request['company_name'];
        $organization->contact_name = $request['contact_name'];
        $organization->mobile_number = $request['mobile_number'];
        $organization->email = $request['email'];
        $organization->website = $request['website'];
        $organization->languages = $request['languages'];
        if (isset($request['logo'])) {
            $organization->logo = $request['logo'];
        }
        $organization->seat_alloted = $request['seat_alloted'];
        $organization->timezone = $request['timezone'];
        $organization->validity_date = $request['validity_date'];
        $organization->is_testing    = $request['is_testing'] ?? 0;
        $organization->save();

        return $organization;
    }




    public function getLogoAttribute()
    {
        if (!empty($this->attributes['logo'])) {
            return asset('storage/'.$this->attributes['logo']) ;
        }
        return asset('images/user.jpeg');
    }

    /**
     * Function to get Organization Key.
     *
     * @param Organization $organization
     * @throws \Exception
     */
    public static function getOrganizationKey(Organization $organization)
    {
        try {
            return $organization->surbo_unique_key ?? '';
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Function to organization details by id.
     *
     * @param integer $organizationId
     * @throws \Exception
     * @return Organization
     */
    public static function getOrganizationDetails($organizationId)
    {
        try {
            return Organization::find($organizationId);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function scopeActive($query)
    {
       return $query->where('status', self::ACTIVE);
    }

    public function user()
    {
        return $this->hasMany(User::class,'organization_id','id');
    }
//    public function getUserIdsAttribute()
//    {
//        return $this->user->pluck('id');
//    }

    public function timeoutSettings()
    {
        $instance= $this->hasOne(PermissionSetting::class,'organization_id','id');
        $instance->where('permission_id',config('constants.PERMISSION.TIMEOUT'));
        return $instance;
    }

    /**
     * Function to get org list.
     *
     * @throws \Exception
     * @return Collection
     */
    public static function getOrgList()
    {
        try {
            return Organization::select('id', 'company_name')->get();
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to fetch all languages marking org lanaguages .
     *
     * @param Organization $org_detail
     */
    public static function findLanguages($org_detail)
    {
        try {
            $org_langauges = [];
            $languages = (config('config.languages'));
            $selected_laguages = json_decode($org_detail->languages, true);

            foreach($languages as $key=>$lang) {
                if (!empty($selected_laguages) && in_array($key, $selected_laguages)) {
                    $org_langauges[$key] = ['value' => true, 'label'=>$lang];
                } else {
                    $org_langauges[$key] = ['value' => false, 'label'=>$lang];
                }
            }
            return $org_langauges;
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to find organization languages.
     *
     * @param integer $organizationId
     * @throws \Exception
     */
    public static function getLanguagesByOrgId($organizationId)
    {
        try {
            $languages = self::find($organizationId)->languages;
            $languages = json_decode($languages, true);
            $orgLanguages = [];

            if (!empty($languages)) {
                foreach($languages as $lang) {
                    $orgLanguages[$lang] = config('config.languages.'.$lang);
                }
            } else {
                $defaultLanguage = config('config.default_language');
                $orgLanguages[$defaultLanguage] = config('config.languages.'.$defaultLanguage);
            }
            return $orgLanguages;
        } catch(\Exception $exception) {
            throw $exception;
        }
    }

    public static function getOrganizationListWithinValidDate($validityDate){

        return self::whereIn('validity_date', $validityDate)->orderBy('validity_date')->get();
    }

    public static function changeOrganizationsStatus($expiredOrganizationId){

      return self::whereIn('id', $expiredOrganizationId)->update(['status'=>self::STATUS_DEACTIVE]);
    }

    /**
     * Organization list which has users
     *
     * @return type
     */
    public static function getOrganizationListWithUsers()
    {
        return self::active()->whereHas('user', function($query) {
            $query->where('status', User::IS_ACTIVE);
        })->orderBy('created_at', 'desc')->get();
    }
}
