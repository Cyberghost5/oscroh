<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\ListsController;
use App\Http\Controllers\StreamsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\WithdrawalsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\StoriesController;
use App\Http\Controllers\SoundsController;
use App\Http\Controllers\TwoFAController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin routes ( Needs to be placed above )

Route::group(['prefix' => 'admin', 'middleware' => ['jsVars', 'admin']], function () {
    Route::get('/users/{id}/impersonate', [UserController::class, 'impersonate'])->name('admin.impersonate');
    Route::get('/leave-impersonation', [UserController::class, 'leaveImpersonation'])->name('admin.leaveImpersonation');
    Route::get('/clear-app-cache', [GenericController::class, 'clearAppCache'])->name('admin.clear.cache');
    Route::get('/clear-optimize-cache', [GenericController::class, 'clearOptimizedCache'])->name('admin.clear.optimize');
    Route::get('/create-storage-symlink', [GenericController::class, 'createStorageSymlink'])->name('admin.storage.symlink');
    Route::get('/generate-sitemap', function () {
        \Artisan::call('generateSitemap');
        return redirect('/sitemap.xml');
    })->name('admin.sitemap.generate');

    Route::post('/withdrawals/{withdrawalId}/approve', [WithdrawalsController::class, 'approveWithdrawal'])->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{withdrawalId}/reject', [WithdrawalsController::class, 'rejectWithdrawal'])->name('admin.withdrawals.reject');
});

// Home & contact page
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/contact', [GenericController::class, 'contact'])->name('contact');
Route::post('/contact/send', [GenericController::class, 'sendContactMessage'])->name('contact.send');

// Language switcher route
Route::get('language/{locale}', [GenericController::class, 'setLanguage'])->name('language');

/* Auth Routes + Verify password */
Auth::routes(['verify'=>true]);
Route::get('email/verify', [GenericController::class, 'userVerifyEmail'])->name('verification.notice');
Route::post('resendVerification', [GenericController::class, 'resendConfirmationEmail'])->name('verfication.resend');
// Social Auth login / register
Route::get('socialAuth/{provider}', [LoginController::class, 'redirectToProvider'])->name('social.login.start');
Route::get('socialAuth/{provider}/callback', [LoginController::class, 'handleProviderCallback'])->name('social.login.callback');

/*
 * (User) Protected routes
 */
Route::group(['middleware' => ['auth', 'verified', '2fa']], function () {
    // Settings panel routes
    Route::group(['prefix' => 'my', 'as' => 'my.'], function () {

        /*
         * (My) Settings
         */
        // Deposit - Payments
        Route::post('/settings/deposit/generateStripeSession', [PaymentsController::class, 'generateStripeSession'])->name('settings.deposit.generateStripeSession');
        Route::post('/settings/flags/save', [SettingsController::class, 'updateFlagSettings'])->name('settings.flags.save');
        Route::post('/settings/profile/save', [SettingsController::class, 'saveProfile'])->name('settings.profile.save');
        Route::post('/settings/rates/save', [SettingsController::class, 'saveRates'])->name('settings.rates.save');
        Route::post('/settings/profile/upload/{uploadType}', [SettingsController::class, 'uploadProfileAsset'])->name('settings.profile.upload');
        Route::post('/settings/profile/remove/{assetType}', [SettingsController::class, 'removeProfileAsset'])->name('settings.profile.remove');
        Route::post('/settings/save', [SettingsController::class, 'updateUserSettings'])->name('settings.save');
        Route::post('/settings/verify/upload', [SettingsController::class, 'verifyUpload'])->name('settings.verify.upload');
        Route::post('/settings/verify/upload/delete', [SettingsController::class, 'deleteVerifyAsset'])->name('settings.verify.delete');
        Route::post('/settings/verify/save', [SettingsController::class, 'saveVerifyRequest'])->name('settings.verify.save');
        Route::get('/settings/privacy/countries', [SettingsController::class, 'getCountries'])->name('settings.verify.countries');
        Route::post('/settings/taxes/save', [SettingsController::class, 'addUserTaxInformation'])->name('settings.taxes.save');

        // Profile save
        Route::get('/settings/{type?}', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/account/save', [SettingsController::class, 'saveAccount'])->name('settings.account.save');

        /*
         * (My) Notifications
         */
        Route::get('/notifications/{type?}', [NotificationsController::class, 'index'])->name('notifications');

        /*
         * (My) Messenger
         */
        Route::group(['prefix' => 'messenger', 'as' => 'messenger.'], function () {
            Route::get('/', [MessengerController::class, 'index'])->name('get');
            Route::get('/fetchContacts', [MessengerController::class, 'fetchContacts'])->name('fetch');
            Route::get('/fetchMessages/{userID}', [MessengerController::class, 'fetchMessages'])->name('fetch.user');
            Route::post('/sendMessage', [MessengerController::class, 'sendMessage'])->name('send');
            Route::delete('/delete/{commentID}', [MessengerController::class, 'deleteMessage'])->name('delete');
            Route::post('/authorizeUser', [MessengerController::class, 'authorizeUser'])->name('authorize');
            Route::post('/markSeen', [MessengerController::class, 'markSeen'])->name('mark');
        });
        /*
         * (My) Bookmarks
         */
        Route::any('/bookmarks/{type?}', [BookmarksController::class, 'index'])->name('bookmarks');
//        Route::get('/bookmarks/{type}',[BookmarksController::class, 'filterBookmarks'])->name('bookmarks.filter');

        /*
         * (My) Lists
         */
        Route::group(['prefix' => '', 'as' => 'lists.'], function () {
            Route::get('/lists', [ListsController::class, 'index'])->name('all');
            Route::post('/lists/save', [ListsController::class, 'saveList'])->name('save');
            Route::get('/lists/{list_id}', [ListsController::class, 'showList'])->name('show');
            Route::delete('/lists/delete', [ListsController::class, 'deleteList'])->name('delete');
            Route::post('/lists/members/save', [ListsController::class, 'addListMember'])->name('members.save');
            Route::delete('/lists/members/delete', [ListsController::class, 'deleteListMember'])->name('members.delete');
            Route::post('/lists/members/clear', [ListsController::class, 'clearList'])->name('members.clear');
            Route::post('/lists/manage/follows', [ListsController::class, 'manageUserFollows'])->name('manage.follows');
        });

        // (My) Streams routes
        Route::group(['prefix' => 'streams', 'as' => 'streams.'], function () {
            Route::get('', [StreamsController::class, 'index'])->name('get');
            Route::post('init', [StreamsController::class, 'initStream'])->name('init');
            Route::post('edit', [StreamsController::class, 'saveStreamDetails'])->name('edit');
            Route::post('stop', [StreamsController::class, 'stopStream'])->name('stop');
            Route::delete('delete', [StreamsController::class, 'deleteStream'])->name('delete');
            Route::post('poster-upload', [StreamsController::class, 'posterUpload'])->name('poster.upload');
            Route::get('broadcast', [StreamsController::class, 'liveKitBroadCast'])->name('livekit.broadcast');
            Route::post('livekit/token', [StreamsController::class, 'generateToken'])->name('livekit.token');
        });

        Route::group(['prefix' => '', 'as' => 'polls.'], function () {
            Route::post('/polls/save', [ListsController::class, 'saveList'])->name('save');
        });

    });

    Route::post('authorizeStreamPresence', [StreamsController::class, 'authorizeUser'])->name('public.stream.authorizeUser');
    Route::post('stream/comments/add', [StreamsController::class, 'addComment'])->name('public.stream.comment.add');
    Route::delete('stream/comments/delete', [StreamsController::class, 'deleteComment'])->name('public.stream.comment.delete');
    Route::get('stream/archive/{streamID}/{slug}', [StreamsController::class, 'getVod'])->name('public.vod.get');
    Route::get('stream/{streamID}/{slug}', [StreamsController::class, 'getStream'])->name('public.stream.get');

    Route::post('/report/content', [ListsController::class, 'postReport'])->name('report.content');

    Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
        Route::post('/initiate', [PaymentsController::class, 'initiatePayment'])->name('initiatePayment');
        Route::post('/initiate/validate', [PaymentsController::class, 'paymentInitiateValidator'])->name('initiatePaymentValidator');
        Route::get('/paypal/status', [PaymentsController::class, 'executePaypalPayment'])->name('executePaypalPayment');
        Route::get('/stripe/status', [PaymentsController::class, 'getStripePaymentStatus'])->name('checkStripePaymentStatus');
        Route::get('/coinbase/status', [PaymentsController::class, 'checkAndUpdateCoinbaseTransaction'])->name('checkCoinBasePaymentStatus');
        Route::get('/nowpayments/status', [PaymentsController::class, 'checkAndUpdateNowPaymentsTransaction'])->name('checkNowPaymentStatus');
        Route::get('/ccbill/status', [PaymentsController::class, 'processCCBillTransaction'])->name('checkCCBillPaymentStatus');
        Route::get('/paystack/status', [PaymentsController::class, 'verifyPaystackTransaction'])->name('checkPaystackPaymentStatus');
        Route::get('/mercado/status', [PaymentsController::class, 'verifyMercadoTransaction'])->name('checkMercadoPaymentStatus');
        Route::get('/verotel/status', [PaymentsController::class, 'verifyVerotelTransaction'])->name('checkVerotelPaymentStatus');
        Route::get('/razorpay/status', [PaymentsController::class, 'verifyRazorPayTransaction'])->name('checkRazorPayPaymentStatus');
    });

    // Feed routes
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/feed/posts', [FeedController::class, 'getFeedPosts'])->name('feed.posts');

    // File uploader routes
    Route::group(['prefix' => 'attachment', 'as' => 'attachment.'], function () {
        Route::post('/upload/{type}', [AttachmentController::class, 'upload'])->name('upload');
        Route::post('/uploadChunked/{type}', [AttachmentController::class, 'uploadChunk'])->name('upload.chunked');
        Route::post('/remove', [AttachmentController::class, 'removeAttachment'])->name('remove');
    });

    // Posts routes
    Route::group(['prefix' => 'posts', 'as' => 'posts.'], function () {
        Route::post('/save', [PostsController::class, 'savePost'])->name('save');
        Route::get('/create', [PostsController::class, 'create'])->name('create');
        Route::get('/edit/{post_id}', [PostsController::class, 'edit'])->name('edit');
        Route::get('/{post_id}/{username}', [PostsController::class, 'getPost'])->name('get');
        Route::get('/comments', [PostsController::class, 'getPostComments'])->name('get.comments');
        Route::post('/comments/add', [PostsController::class, 'addNewComment'])->name('add.comments');
        Route::post('/comments/edit', [PostsController::class, 'editComment'])->name('edit.comments');
        Route::delete('/comments/delete', [PostsController::class, 'deleteComment'])->name('delete.comments');

        Route::post('/reaction', [PostsController::class, 'updateReaction'])->name('react');
        Route::post('/bookmark', [PostsController::class, 'updatePostBookmark'])->name('bookmark');
        Route::post('/pin', [PostsController::class, 'updatePostPin'])->name('pin');
        Route::delete('/delete', [PostsController::class, 'deletePost'])->name('delete');

        Route::post('/polls/vote', [PostsController::class, 'userPollVote'])->name('polls.vote');
    });

    // Subscriptions routes
    Route::group(['prefix' => 'subscriptions', 'as' => 'subscriptions.'], function () {
        Route::get('/{subscriptionId}/cancel/{redirectTo}', [SubscriptionsController::class, 'cancelSubscription'])->name('cancel');
    });

    // Withdrawals routes
    Route::group(['prefix' => 'withdrawals', 'as' => 'withdrawals.'], function () {
        Route::post('/request', [WithdrawalsController::class, 'requestWithdrawal'])->name('request');
        Route::get('/onboarding', [WithdrawalsController::class, 'onboarding'])->name('onboarding');
    });

    // Invoices routes
    Route::group(['prefix' => 'invoices', 'as' => 'invoices.'], function () {
        Route::get('/{id}', [InvoicesController::class, 'index'])->name('get');
    });

    // Countries routes
    Route::group(['prefix' => 'countries', 'as' => 'countries.'], function () {
        Route::get('', [GenericController::class, 'countries'])->name('get');
    });

    // Ai routes
    Route::group(['prefix' => 'suggestions', 'as' => 'suggestions.'], function () {
        Route::post('/generate', [AiController::class, 'generateSuggestion'])->name('generate');
    });

    Route::post('/auth/presence-channel', [GenericController::class, 'authorizePresenceChannel'])->name('presence.auth');

    // Private stories routes
    Route::group(['prefix' => 'stories', 'as' => 'stories.'], function () {
        Route::get('/create', [StoriesController::class, 'create'])->name('create');
        Route::post('/create', [StoriesController::class, 'store'])->name('store');
        Route::get('/feed', [StoriesController::class, 'feed'])->name('feed');
        Route::get('/payload/{id}', [StoriesController::class, 'payload'])->name('payload');

        Route::post('/upload', [StoriesController::class, 'upload'])->name('upload');
        Route::post('/view', [StoriesController::class, 'view'])->name('view');

        Route::delete('/delete', [StoriesController::class, 'delete'])->name('delete');
        Route::post('/pin-toggle', [StoriesController::class, 'pinToggle'])->name('pinToggle');
    });

    Route::group(['prefix' => 'sounds', 'as' => 'sounds.'], function () {
        Route::get('/trending', [SoundsController::class, 'trending'])->name('trending');
        Route::get('/search', [SoundsController::class, 'search'])->name('search');
    });

});

// Public story routes
Route::group(['prefix' => 'stories', 'as' => 'stories.'], function () {
    Route::get('/s/{story}', [StoriesController::class, 'share'])->name('share');
    Route::get('/profile/{username}', [StoriesController::class, 'profile'])->name('profile');
    Route::get('/highlights/{username}', [StoriesController::class, 'highlights'])->name('highlights');
});

// Subscriptions routes
Route::group(['prefix' => 'subscriptions', 'as' => 'subscriptions.'], function () {
    Route::get('/{subscriptionId}/cancel/{redirectTo}', [SubscriptionsController::class, 'cancelSubscription'])->name('cancel');
});

// 2FA related routes
Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('device-verify', [TwoFAController::class, 'index'])->name('2fa.index');
    Route::post('device-verify', [TwoFAController::class, 'store'])->name('2fa.post');
    Route::get('device-verify/reset', [TwoFAController::class, 'resend'])->name('2fa.resend');
    Route::delete('device-verify/delete', [TwoFAController::class, 'deleteDevice'])->name('2fa.delete');
});

Route::any('beacon/{type}', [StatsController::class, 'sendBeacon'])->name('beacon.send');

Route::post('payment/stripeStatusUpdate', [PaymentsController::class, 'stripePaymentsHook'])->name('stripe.payment.update');

Route::post('payment/stripeConnectStatusUpdate', [PaymentsController::class, 'stripeConnectHook'])->name('stripeConnect.payment.update');

Route::post('payment/paypalStatusUpdate', [PaymentsController::class, 'paypalPaymentsHook'])->name('paypal.payment.update');

Route::post('payment/coinbaseStatusUpdate', [PaymentsController::class, 'coinbaseHook'])->name('coinbase.payment.update');

Route::post('payment/nowPaymentsStatusUpdate', [PaymentsController::class, 'nowPaymentsHook'])->name('nowPayments.payment.update');

Route::post('payment/ccBillPaymentStatusUpdate', [PaymentsController::class, 'ccBillHook'])->name('ccBill.payment.update');

Route::post('payment/paystackPaymentStatusUpdate', [PaymentsController::class, 'paystackHook'])->name('paystack.payment.update');

Route::post('payment/mercadoPaymentStatusUpdate', [PaymentsController::class, 'mercadoHook'])->name('mercado.payment.update');

Route::get('payment/verotelPaymentStatusUpdate', [PaymentsController::class, 'verotelHook'])->name('verotel.payment.update');

Route::post('payment/razorPayPaymentStatusUpdate', [PaymentsController::class, 'razorPayHook'])->name('razorpay.payment.update');

Route::post('transcoding/coconut/update', [AttachmentController::class, 'handleCoconutHook'])->name('transcoding.coconut.update');

// Install & upgrade routes
Route::get('/install', [InstallerController::class, 'install'])->name('installer.install');
Route::post('/install/savedbinfo', [InstallerController::class, 'testAndSaveDBInfo'])->name('installer.savedb');
Route::post('/install/beginInstall', [InstallerController::class, 'beginInstall'])->name('installer.beginInstall');
Route::get('/install/finishInstall', [InstallerController::class, 'finishInstall'])->name('installer.finishInstall');
Route::get('/update', [InstallerController::class, 'upgrade'])->name('installer.update');
Route::post('/update/doUpdate', [InstallerController::class, 'doUpgrade'])->name('installer.doUpdate');

// (Feed/Search) Suggestions filter
Route::post('/suggestions/members', [FeedController::class, 'filterSuggestedMembers'])->name('suggestions.filter');

// Public random feed routes
Route::get('/public-feed', [FeedController::class, 'publicIndex'])->name('feed.public');
Route::get('/public-feed/posts', [FeedController::class, 'getPublicFeedPosts'])->name('feed.public.posts');

// Public pages
Route::get('/pages/{slug}', [PublicPagesController::class, 'getPage'])->name('pages.get');

Route::get('/search', [SearchController::class, 'index'])->name('search.get');
Route::get('/search/posts', [SearchController::class, 'getSearchPosts'])->name('search.posts');
Route::get('/search/users', [SearchController::class, 'getUsersSearch'])->name('search.users');
Route::get('/search/streams', [SearchController::class, 'getStreamsSearch'])->name('search.streams');

Route::post('/markBannerAsSeen', [GenericController::class, 'markBannerAsSeen'])->name('banner.mark.seen');

// Public profile
Route::get('/{username}', [ProfileController::class, 'index'])->where('username', '^(?!public-feed$).+')->name('profile');
Route::get('/{username}/posts', [ProfileController::class, 'getUserPosts'])->where('username', '^(?!public-feed$).+')->name('profile.posts');
Route::get('/{username}/streams', [ProfileController::class, 'getUserStreams'])->where('username', '^(?!public-feed$).+')->name('profile.streams');

Route::fallback(function () {
    abort(404);
});
