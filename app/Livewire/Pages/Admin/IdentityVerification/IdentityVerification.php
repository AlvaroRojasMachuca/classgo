<?php

namespace App\Livewire\Pages\Admin\IdentityVerification;

use App\Jobs\SendNotificationJob;
use App\Livewire\Forms\Admin\User\UserForm;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Scopes\ActiveScope;
use App\Models\User;
use App\Models\UserIdentityVerification;
use App\Notifications\EmailNotification;
use App\Services\IdentityService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

class IdentityVerification extends Component {
    use WithPagination;

    public      UserForm $form;
    public      $search             = '';
    public      $sortby             = 'desc';
    public      $user_id            = '';
    public      $verification       = 'non_verified';
    public      $user;

    private ?IdentityService $userIdentity = null;
    public function boot()
    {
        $this->userIdentity     = new IdentityService(Auth::user());
        $this->user         = Auth::user();
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $users = UserIdentityVerification::with([
            'address' => function ($query) {
                $query->with('country');
            },
            'profile:id,user_id,verified_at'
        ]);
        if (!empty($this->verification)) {
            if ($this->verification === 'verified') {
                $users = $users->whereNotNull('parent_verified_at');
            } elseif ($this->verification === 'non_verified') {
                $users = $users->whereNull('parent_verified_at');
            }
        }
        if (!empty($this->search)) {
            $users->where(function ($sub_query) {
                $sub_query->whereFullText('name', $this->search);
            });
        }
        $users = $users->orderBy('id', $this->sortby)->paginate(setting('_general.per_page_opt') ?? 10);
        return view('livewire.pages.admin.identity-verification.identitiy_verification', compact('users'));
    }

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2' );
    }


    public function updated($propertyName)
    {
        if (in_array($propertyName, ['verification', 'search', 'sortby'])) {
            $this->resetPage();
        }
    }

    #[On('verified-template')]
    public function verifiedTemplate($params = [])
    {
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        $date = now();
        if (!empty($params['id'])) {
            $adminExists = User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->where('id', $params['id'])->exists();

            if ($adminExists ) {
                $this->dispatch('showAlertMessage', [
                    'type'      => 'error',
                    'title'     => __('admin/general.error_title'),
                    'message'   => __('admin/general.not_allowed')
                ]);
                return;
            } else {
                  if($params['type'] == 'accepted'){
                    Profile::where('user_id', $params['id'])->update(['verified_at' => $date]);
                  }
                  $userIdentityVerification  =  UserIdentityVerification::where('user_id', $params['id'])->first();
                  $userIdentityVerification->status = $params['type'];
                  $userIdentityVerification->save();
                  if($params['type'] == 'accepted'){
                        dispatch(new SendNotificationJob('identityVerificationApproved', $userIdentityVerification->user, ['name'=>$userIdentityVerification?->name]));
                    } else {
                        dispatch(new SendNotificationJob('identityVerificationRejected', $userIdentityVerification->user, ['name'=>$userIdentityVerification?->name]));
                  }
                  if($params['type'] == 'rejected'){
                    $userIdentityVerification->address()->delete();
                    $userIdentityVerification->delete();
                  }
            }
        }
    }

    public function toggleStatus($userId, $newStatus)
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $adminExists = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->where('id', $userId)->exists();

        if ($adminExists) {
            $this->dispatch('showAlertMessage', [
                'type'      => 'error',
                'title'     => __('admin/general.error_title'),
                'message'   => __('admin/general.not_allowed')
            ]);
            return;
        }

        $date = now();
        if ($newStatus == 'accepted') {
            Profile::where('user_id', $userId)->update(['verified_at' => $date]);
        }

        $userIdentityVerification = UserIdentityVerification::where('user_id', $userId)->first();
        $userIdentityVerification->status = $newStatus;
        $userIdentityVerification->save();

        if ($newStatus == 'accepted') {
            dispatch(new SendNotificationJob('identityVerificationApproved', $userIdentityVerification->user, ['name' => $userIdentityVerification?->name]));
        } else {
            dispatch(new SendNotificationJob('identityVerificationRejected', $userIdentityVerification->user, ['name' => $userIdentityVerification?->name]));
            $userIdentityVerification->address()->delete();
            $userIdentityVerification->delete();
        }

        $this->dispatch('showAlertMessage', [
            'type'      => 'success',
            'title'     => __('general.success_title'),
            'message'   => __('general.status_updated_successfully')
        ]);
    }

}
