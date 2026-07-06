<?php

use App\Http\Controllers\API\V1\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\API\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\API\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\API\V1\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\API\V1\Admin\MembershipPlanController as AdminMembershipPlanController;
use App\Http\Controllers\API\V1\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\API\V1\Admin\PublicationCategoryController as AdminPublicationCategoryController;
use App\Http\Controllers\API\V1\Admin\PublicationController as AdminPublicationController;
use App\Http\Controllers\API\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\API\V1\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\API\V1\Admin\TrainingEventController as AdminTrainingEventController;
use App\Http\Controllers\API\V1\Auth\MemberAuthController;
use App\Http\Controllers\API\V1\Member\AnnouncementController;
use App\Http\Controllers\API\V1\Member\LibraryController;
use App\Http\Controllers\API\V1\Member\MembershipController;
use App\Http\Controllers\API\V1\Member\PaymentController;
use App\Http\Controllers\API\V1\Member\ProfileController;
use App\Http\Controllers\API\V1\Member\PublicationController;
use App\Http\Controllers\API\V1\Member\ReceiptController;
use App\Http\Controllers\API\V1\Member\ResourceController;
use App\Http\Controllers\API\V1\Member\WalletController;
use App\Http\Controllers\API\V1\TrainingEventController;
use App\Http\Controllers\API\V1\InvoiceStudioController;
use App\Http\Controllers\API\V1\Webhook\XpouchWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [MemberAuthController::class, 'register'])->name('registerMemberAccount');
    Route::post('/login', [MemberAuthController::class, 'login'])->name('loginUserAccount');
    Route::get('/training-events', [TrainingEventController::class, 'index'])->name('listPublishedTrainingEvents');
    Route::get('/training-events/{slug}', [TrainingEventController::class, 'show'])->name('getPublishedTrainingEvent');

    Route::middleware('auth:sanctum')->prefix('studio')->group(function () {
        Route::get('/me', [MemberAuthController::class, 'me']);
        Route::post('/logout', [MemberAuthController::class, 'logout']);
        Route::get('/invoices', [InvoiceStudioController::class, 'invoices']);
        Route::post('/invoices', [InvoiceStudioController::class, 'store']);
        Route::delete('/invoices/{invoice}', [InvoiceStudioController::class, 'destroy']);
        Route::get('/branding', [InvoiceStudioController::class, 'branding']);
        Route::put('/branding', [InvoiceStudioController::class, 'saveBranding']);
    });

    Route::middleware(['auth:sanctum', 'member'])->group(function () {
        Route::post('/logout', [MemberAuthController::class, 'logout'])->name('logoutMemberAccount');

        Route::get('/member/profile', [ProfileController::class, 'show'])->name('getMemberProfile');
        Route::put('/member/profile', [ProfileController::class, 'update'])->name('updateMemberProfile');
        Route::get('/member/membership', [MembershipController::class, 'index'])->name('getMemberMembershipSummary');
        Route::post('/member/renewals/initiate', [MembershipController::class, 'renew'])->name('initiateMembershipRenewalPayment');
        Route::get('/member/payments', [PaymentController::class, 'index'])->name('listMemberPayments');
        Route::get('/member/receipts', [ReceiptController::class, 'index'])->name('listMemberReceipts');
        Route::get('/member/wallet', [WalletController::class, 'show'])->name('getMemberWallet');
        Route::post('/member/wallet/deposit', [WalletController::class, 'deposit'])->name('initiateMemberWalletDeposit');

        Route::get('/resources', [ResourceController::class, 'index'])->name('listMemberResources');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('listMemberAnnouncements');
        Route::get('/publications', [PublicationController::class, 'index'])->name('listPublishedPublications');
        Route::get('/publications/{publication}', [PublicationController::class, 'show'])->name('getPublicationDetails');
        Route::post('/publications/{publication}/purchase', [PublicationController::class, 'purchase'])->name('initiatePublicationPurchasePayment');
        Route::get('/library', [LibraryController::class, 'index'])->name('listMemberDigitalLibrary');
        Route::get('/library/{publication}/read', [LibraryController::class, 'read'])->name('readPurchasedPublication');
        Route::get('/library/{publication}/download', [LibraryController::class, 'download'])->name('downloadPurchasedPublication');
    });

    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logoutAdminAccount');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('getAdminDashboardMetrics');

        Route::apiResource('members', AdminMemberController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy'])
            ->names([
                'index' => 'listMembersForAdmin',
                'store' => 'createMemberByAdmin',
                'show' => 'getMemberRecordForAdmin',
                'update' => 'updateMemberRecordByAdmin',
                'destroy' => 'deleteMemberRecordByAdmin',
            ]);
        Route::post('/members/{member}/activate', [AdminMemberController::class, 'activate'])->name('activateMemberAccount');
        Route::post('/members/{member}/suspend', [AdminMemberController::class, 'suspend'])->name('suspendMemberAccount');

        Route::apiResource('membership-plans', AdminMembershipPlanController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names([
                'index' => 'listMembershipPlans',
                'store' => 'createMembershipPlan',
                'update' => 'updateMembershipPlan',
                'destroy' => 'deleteMembershipPlan',
            ]);
        Route::apiResource('resources', AdminResourceController::class)
            ->only(['index', 'store', 'update'])
            ->names([
                'index' => 'listResourceDocumentsForAdmin',
                'store' => 'uploadResourceDocument',
                'update' => 'updateResourceDocument',
            ]);
        Route::apiResource('announcements', AdminAnnouncementController::class)
            ->only(['index', 'store', 'update'])
            ->names([
                'index' => 'listAnnouncementsForAdmin',
                'store' => 'createAnnouncement',
                'update' => 'updateAnnouncement',
            ]);
        Route::apiResource('publication-categories', AdminPublicationCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->names([
                'index' => 'listPublicationCategories',
                'store' => 'createPublicationCategory',
                'update' => 'updatePublicationCategory',
                'destroy' => 'deletePublicationCategory',
            ]);
        Route::apiResource('publications', AdminPublicationController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy'])
            ->names([
                'index' => 'listPublicationsForAdmin',
                'store' => 'uploadPublication',
                'show' => 'getPublicationForAdmin',
                'update' => 'updatePublication',
                'destroy' => 'deletePublication',
            ]);
        Route::get('/publications/{publication}/read', [AdminPublicationController::class, 'read'])->name('readPublicationForAdmin');
        Route::apiResource('training-events', AdminTrainingEventController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['training-events' => 'trainingEvent'])
            ->names([
                'index' => 'listTrainingEventsForAdmin',
                'store' => 'createTrainingEvent',
                'update' => 'updateTrainingEvent',
                'destroy' => 'deleteTrainingEvent',
            ]);
        Route::post('/training-events/{trainingEvent}', [AdminTrainingEventController::class, 'update'])->name('updateTrainingEventWithImages');

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('listAllPayments');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('getPaymentDetails');
        Route::get('/reports/revenue', [AdminReportController::class, 'revenue'])->name('getRevenueReport');
        Route::get('/reports/publications', [AdminReportController::class, 'publications'])->name('getPublicationSalesReport');
        Route::get('/reports/downloads', [AdminReportController::class, 'downloads'])->name('getPublicationDownloadReport');
    });

    Route::post('/webhooks/xpouch', XpouchWebhookController::class)->name('processXpouchPaymentWebhook');
});
