<?php

namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Model;

class ApplicationComposer
{
    /**
     * The user repository implementation.
     *
     * @var Model
     */
    private $user;
    
    public $organizationId;

    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            if (!Gate::allows('superadmin', Auth::user()->role_id)) {
                $this->organizationId = Auth::user()->organization_id;
            } else {
                $this->organizationId = 0;
            }
            $view->with('organizationId', $this->organizationId);
        }
    }
}
