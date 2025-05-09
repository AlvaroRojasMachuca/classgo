<?php

namespace App\Livewire\Pages\Common\ProfileSettings;

use App\Jobs\SendNotificationJob;
use App\Livewire\Forms\Common\ProfileSettings\IdentityVerificationForm;
use App\Models\Country;
use App\Models\User;
use App\Services\IdentityService;
use App\Services\ProfileService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class IdentityVerification extends Component {

    use WithFileUploads;
    public IdentityVerificationForm $form;
    private $identityService;
    public $identityInfo;
    public $identity;
    public $isLoading = true;
    public $personalPhoto;
    public $docs, $existingDocs = [];
    public $isUploading = false;
    public $verified = false;
    public $user = '';
    public $countries = null;
    public $states;
    public $allowImgFileExt = [];
    public $fileExt = '';
    public $allowImageSize  = '';
    public $data;
    public $profile;
    public $emailTemplate;
    public $hasStates = false;
    public $activeRoute;

    private ?IdentityService $userIdentity = null;
    private ?ProfileService $profileService = null;

    public function boot()
    {
        $this->userIdentity     = new IdentityService(Auth::user());
        $this->profileService   = new ProfileService(Auth::user()->id);
        $this->user             = Auth::user();
    }

    public function loadData()
    {
        $this->isLoading            = false;
        $this->dispatch('loadPageJs');
    }

    public function mount()
    {
        $this->activeRoute       = Route::currentRouteName();
        $this->profile           = $this->profileService->getUserProfile();
        $this->countries         = Country::get(['id','name']) ?? [];;
        $this->emailTemplate      = setting('_lernen.for_role') ?? (object)['status' => 'both'];
        $image_file_ext          = setting('_general.allowed_image_extensions') ?? 'jpg,png';
        $image_file_size         = (int) (setting('_general.max_image_size') ?? '5');
        $this->allowImageSize    = !empty( $image_file_size ) ? $image_file_size : '5';
        $this->allowImgFileExt   = !empty( $image_file_ext ) ?  explode(',', $image_file_ext) : [];
        $this->fileExt           = fileValidationText($this->allowImgFileExt);

        if(Auth::user()->profile?->created_at == Auth::user()->profile?->updated_at){
            Session::flash('error', __('general.incomplete_profile_error'));
            return $this->redirect(route(Auth::user()->role.'.profile.personal-details'), navigate:true);
        }

        $this->dispatch('initSelect2', target: '.am-select2' );
    }

    public function updatedForm($value, $key)
    {
        if( $key == 'countryName' ) {
            $country = Country::where('short_code',$value)->select('id')->first();
            $this->form->country =  $country?->id;
        } elseif( in_array($key, ['image', 'identificationCard','transcript']) ) {
            $mimeType = $value->getMimeType();
            $type = explode('/', $mimeType);
            if($type[0] != 'image') {
                $this->dispatch('showAlertMessage', type: `error`, message: __('validation.invalid_file_type', ['file_types' => fileValidationText($this->allowImgFileExt)]));
                $this->form->{$key} = null;
                return;
            }
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->states = null;
        $this->identity         = $this->userIdentity->getUserIdentityVerification();
        if(!empty($this->form->country)){
            $this->states =$this->userIdentity->countryStates($this->form->country);
            if($this->states->isNotEmpty()){
                $this->hasStates = true;
                $this->dispatch('initSelect2', target: '#country_state' );
            } else {
                $this->hasStates = false;
            }
        }
        $enableGooglePlaces = '0';
          \Log::info('Valor de enableGooglePlaces:', ['value' => $enableGooglePlaces]);
        $googleApiKey           = setting('_api.google_places_api_key');
        return view('livewire.pages.common.profile-settings.identity-verification',compact('enableGooglePlaces','googleApiKey'));
    }

    public function removeMedia($type)
    {
        match ($type) {
            'personal_photo'        => $this->form->removePhoto(),
            'identificationCard'    => $this->form->removeIdentificationCard(),
            'transcript'            => $this->form->removeTranscript()

        };
    }

    #[On('cancel-identity')]
    public function removeIdentity()
    {
        $this->userIdentity->deleteUserAddress($this->identity->id);
        $this->userIdentity->deleteUserIdentityVerification();
        $this->form->reset();
        $this->dispatch('initSelect2', target: '.am-select2' );
    }


    public function updateInfo()
    {
        
        $this->data = $this->form->updateInfo($this->hasStates);
       
        $response = isDemoSite();
   
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
           
       try {
           $this->data['address']['lat'] = 0.0;
        $this->data['address']['long'] = 0.0;
    DB::beginTransaction();
    
    \Log::info('/log: entro al beginTransaction');
    $userIdentity = $this->userIdentity->setUserIdentityVerification($this->data['identityInfo']);
    $this->userIdentity->setUserAddress($userIdentity?->id, $this->data['address']);
    
    DB::commit();
} catch (\Illuminate\Validation\ValidationException $e) {
    
       \Log::error('/log: error en la validación', ['errors' => $e->errors()]);
    
    // Muestra los errores de validación detalladamente
    dd($e->errors());
    
    DB::rollBack();

    


}
        $this->data['identityInfo']['gender'] = $this->profile?->gender;
        $this->data['identityInfo']['email'] = Auth::user()->email;
        $this->data['identityInfo']['role'] = Auth::user()->role;
        dispatch(new SendNotificationJob('identityVerificationRequest',User::admin(), $this->data)); 
        if (Auth::user()->hasRole('student') && $this->emailTemplate?->status !== 'both') {
            return;
        }
        dispatch(new SendNotificationJob('identityVerificationRequest', Auth::user(), $this->data));
    }

}
