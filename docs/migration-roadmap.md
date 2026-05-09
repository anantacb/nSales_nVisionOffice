# Migration Roadmap — nsalesoffice → nVisionOffice

## Context

You maintain two codebases for the same product:

- **Old:** `/Users/nsales/Projects/nsalesoffice` — Laravel 5.8 + Vue 2 (Vuex, Mix). Production. ~131 controllers, ~231 Eloquent repositories, 197 models, 43 jobs, 61 Vue domains, 13 external integrations.
- **New:** `/Users/nsales/Projects/nVisionOffice` — Laravel 12 + Vue 3 (Pinia, Vite, JWT). Approximately 40% of feature surface ported. Core platform (auth, company, customer, order, users, settings, email, items-basic) is in place. Multi-tenant infra (`SetCompanyDatabaseConnection` middleware) is wired. Repository pattern is preserved.

**Goal of this document:** an ordered, technical-dependency-driven roadmap for porting the remaining ~60% of features. Each phase names what to port, the source files in the old project, where they land in the new project, and what unblocks afterwards. This is a roadmap, not an implementation plan — each phase should later get its own detailed spec before code is written.

**Why dependency order:** lowest rework risk. Cross-cutting infra (file storage, notifications, broadcasting, helpers) is shared by most features; porting it first means feature ports drop in cleanly. Within feature work, deeper Product → Order → E-commerce ordering means downstream domains (campaigns, PDF catalogue, Shopify) can lean on solid foundations rather than being rebuilt twice.

---

## Stack diffs to keep in mind during every port

| Concern | Old | New | Notes |
|---|---|---|---|
| State | Vuex 3 modules | Pinia `defineStore` | Old has only 4 store modules (`company`, `order`, `user`, `pdfCatalogue`); most data is fetched ad-hoc. Don't over-port to Pinia — only promote to a store when shared across views. |
| Build | Laravel Mix | Vite | Asset URLs change; check `vite.config.js`. |
| Routing | Vue Router 3 (`route.js` flat) | Vue Router 4 (`router/index.js`) | Lazy imports use new syntax. |
| Components | `resources/js/components/<Domain>` | `resources/js/views/<kebab-domain>` | Naming convention shift: `CustomerVisits/` → `customer-visit/`. |
| Mixins | `resources/js/Mixins/` | composables in `resources/js/composables/` | Convert each mixin to a composable; only port what's actually used. |
| Auth | session + token | JWT (jwt-auth) + Sanctum fallback | Old `OnePlatformApiAuth` middleware → re-evaluate need. |
| Repositories | `Repositories/Eloquent/Abstraction/BaseEloquentRepository` | `Repositories/Eloquent/Base/BaseRepository` | Same pattern; binding moved to `RepositoryServiceProvider`. |
| Helpers | autoloaded `app/Helpers/*.php` via composer | autoloaded from `app/Helpers/Functions/` | Function files moved into `Functions/` subfolder; class-style helpers stay in `app/Helpers/`. |
| API response shape | `app/Helpers/ApiCommonResponses.php` (fluent) | `app/Transformer/ApiResponseTransformer.php` (static) | Convention changed: static `success/error` methods, no custom code field, pagination separated from data envelope. |

---

## Phase 0 — Cross-cutting infrastructure (must precede most feature work)

These are shared by many domains. Skipping them means re-doing them later under deadline pressure. Verified gaps (as of writing) are listed in the Verified gaps column.

| Item | Old source | Verified gap in new project |
|---|---|---|
| Multi-tenant DB switching | `app/Http/Middleware/SetCompanyDatabaseConnection.php`, `ValidCompanyUser.php` | `SetCompanyDatabaseConnection` exists but refactored — domain→companyId resolution stripped, requires explicit companyId. **`ValidCompanyUser` middleware missing.** Confirm JWT auth path covered. |
| File storage / GCS | `config/filesystems.php`, `app/Helpers/FileUrlGenerator.php`, `FileHelpers.php` | `FileUrlGenerator.php` ported (refactored to use `CompanyService`). **GCS disk not configured** in `config/filesystems.php`. **`FileHelpers` (`checkUrlExists`, `hasDotInFilename`) missing.** |
| Notification service | `app/Services/Notification/*`, `Channel/FirebaseCloudMessagingChannel.php`, `app/Helpers/Notification.php` | **Entire subsystem missing** — no service, channels, repositories, helper. |
| FCM / Firebase REST | `app/Services/FirebaseCloudMessageService.php`, `app/Repositories/Firebase/FcmRestRepository.php` | **Missing.** `kreait/firebase` package not in `composer.json`. `config/services.php` has no `firebase` section. |
| Broadcasting / Pusher | `config/broadcasting.php`, `app/Broadcasting/CompanyBroadcastManager.php`, `CompanyBroadcastJob.php`, `app/Contracts/CompanyBroadcastEvent.php`, `app/Events/Live/*` | Stock `BroadcastServiceProvider` and `config/broadcasting.php` present. **`CompanyBroadcastManager`/`Job`/`Event` interface and per-company Pusher routing missing.** |
| Activity log | `app/Services/ActivityLogService.php`, `app/Jobs/CreateActivityLog.php`, `app/Http/Controllers/ActivityLogController.php`, `app/Models/Company/ActivityLog.php`, `ActivityLogRepository` + interface | **Entire subsystem missing.** |
| Postmark email | `config/services.php` (postmark token via `decryptPostmarkToken()`), `app/Helpers/postmarkTokenEncryptionDecryption.php` | `wildbit/postmark-php` ^6.0 in composer; helper present at `app/Helpers/Functions/postmarkTokenEncryptionDecryption.php`. **Postmark mailer not registered in `config/mail.php` mailers.** Token currently plain env (no `decryptPostmarkToken()` call). |
| Helpers bundle | 11 files (~2060 lines) in `app/Helpers/` | Partial: `FileUrlGenerator.php`, `Helpers.php`, `SqlFormatter.php`, `DbHelpers.php` ported; function files moved to `app/Helpers/Functions/`. **Missing: `ApiCommonResponses.php`, `FileHelpers.php`, `Notification.php`, `DateHelpers.php`, `ArrayHelpers.php`, `DataHelpers.php`** (verify each). |
| API common response shape | `app/Helpers/ApiCommonResponses.php` (fluent: `errorCode()->message()->statusCode()->successResponse([...])`); shape `{success, code, message, ...}` with custom codes (1000=success, 1003=error) | Replaced with `app/Transformer/ApiResponseTransformer.php` (static `success($data, $message, $statusCode)` / `error()` / `pagination()`); shape `{success, message, data, pagination}`. **Decision required:** standardise on the new transformer for all ported controllers (recommended) and rewrite old fluent calls during the port. Don't reintroduce `ApiCommonResponses`. |

**Exit criteria for Phase 0:** a new feature port can call notification, file upload, activity log, and broadcasting without needing to scaffold any of them. Every controller returns through `ApiResponseTransformer`.

---

## Phase 1 — Product domain depth

The new project has `Item` and `ItemAttribute` services as small placeholders. The old project has a full PIM/catalog stack that nearly every commerce feature consumes.

Port in this order:

1. **Item core depth** — variants, images, descriptions, attributes
   - Old: `ItemVariantController`, `ItemImageController`, `ItemAttributeController`, `ItemVariantDescriptionController`, `WebShopTextController` and their repositories
   - Old jobs: `UploadFirebaseItemData`, `RunItemScript`
   - Target: expand `app/Services/Item/`, `app/Services/ItemAttribute/`, add `ItemVariant`, `ItemImage`, `WebShopText` services
2. **Itemgroups + PIM**
   - Old: `ItemgroupController`, `ItemgroupRepository`, `PimItemgroupRepository`, `PimItemgroupItemRepository`, `ItemgroupImageRepository`
   - Old jobs: `UploadFirebaseItemgroupData`, `RunItemgroupScript`
3. **Brands**
   - Old: `BrandController`, `BrandRepository`, frontend `Brand/`
4. **Pricing** — price groups, discounts, lookup
   - Old: `PriceGroupController`, `PriceDiscountController`, `PriceLookupController`
5. **Item assortment + shopping feed**
   - Old: `CustomerAssortmentItem` repos, `ItemShoppingFeedController`
6. **Custom data fields**
   - Old: `CustomDataController`, `CustomDataRepository`

**Exit criteria for Phase 1:** Order, Cart, WebShop, Shopify, and Campaign phases can all reference items/variants/prices without stub data.

---

## Phase 2 — Order domain depth

`Order` already exists at "medium" size in the new project. Old project has order surface ~10× larger (claims, drafts, voucher headers, multi-stage cart, sales/customer/product analytics).

Port in this order:

1. **Cart / Shopping cart** — `CartController`, `ShoppingCartRepository`
2. **Order lifecycle states** — open, draft, failed orders (route handlers in old `web_api.php`)
3. **Order line + voucher header** — `OrderLineRepository`, `OrderheadVoucherRepository`
4. **Claims** — `ClaimController`
5. **Order mail jobs** — `ProcessOrderMail`, `SendOrderConfirmationMail`, `PostOrderConfirmationMail`, `SendAbandonedCartMailsByCompany`, `ProcessReturnOrderMail`
6. **Order analytics dashboards** — `OrderDashboardController`, `OrderBySalesRepController`, `OrderByProductController`, `OrderByCustomerController`, `OrderByWebShopUserController` and the `/total/*`, `/yearly/*` route group
7. **Frontend Order views** expand: Cart, CreateOrder, EditOrder, OrderDetails, dashboard widgets

**Exit criteria for Phase 2:** end-to-end order can be created, paid (stub), confirmed, claimed, and reported on. Unblocks Phase 3 e-commerce flows and Phase 5 Shopify order-webhook ingestion.

---

## Phase 3 — E-commerce / WebShop

Partial scaffolding exists (`WebShopUser`, `WebShopPage`, `WebShopLanguage`, `WebShopText` services). Build out the rest.

1. **WebShop user + language depth** — `WebShopUserController`, `WebShopLanguageController`, `WebShopVoucherController`
2. **Pages + page groups + page builder** — `WebShopPageController`, `WebShopPageGroupController`, `PageBuilderController`, `PageBuilderFrontendController`. Note: page builder routes live in `routes/web.php` and `routes/api_unthrottled.php`.
3. **Shipping** — `WebShopShippingController`, `WebShopShippingIntervalRepository`
4. **Payment gateways scaffolding** — `WebShopPaymentGatewayController` (Bambora wiring deferred to Phase 4)
5. **Vouchers** — `WebShopVoucherController`
6. **Gallery + Inspiration** — `GalleryRepository`, `InspirationRepository`, `GalleryInspirationRepository`

**Exit criteria for Phase 3:** a webshop is browsable end-to-end with pages, products, shipping options, and a working voucher flow (sans real payment).

---

## Phase 4 — External integrations (sequential — each is a large chunk)

These are the highest-risk domains. Tackle individually.

1. **Document API / ERP** — `DocumentApiController`, full `DocumentAPI` namespace (BusinessCentral NTLM/REST/OAuth, e-conomic, Uniconta, plus custom connectors: SOL, DoneByDeer, GeorgJensen, EvaSolo, FnH, GLS). Old uses `Helpers/NTLMSoapClient.php`. New project has `app/Services/DocumentApi/` stub — confirm scope.
2. **Shopify** — `ShopifyService`, `Shopify/ShopifyProductService`, `Shopify/ShopifyOrderWebhookService`, all Shopify mapping repos, jobs (`ProcessShopifyOrderWebhook`, `ChunkWiseShopifyProduct*`), `VerifyShopifyWebhook` middleware, frontend `Shopify/` views
3. **QuickBooks** — `QuickbookApiController`, `QuickBookAuthenticationRepository`, OAuth callback flow
4. **Bambora payments** — payment gateway integration (depends on Phase 3 step 4 scaffolding)

**Exit criteria for Phase 4:** at least one ERP connector verified end-to-end against a sandbox; Shopify webhook can ingest a real order.

---

## Phase 5 — Media & real-time

1. **Mux video** — `MuxController`, `MuxRepository`, `VerifyMuxSignature` middleware, webhook routes
2. **Firebase Realtime DB sync** — `FirebaseRealTimeDatabaseController`, sync jobs (`UploadFirebaseCustomerAssortmentData`, `UploadFirebaseItemAssortmentData`, etc.)
3. **Live streaming** — `LiveController`, `LiveRepository`, `LiveLogRepository`, events (`LiveStatus`, `HighlightProduct`, `ProductPriorityUpdate`, `ToggleDisabled`), job `SendLiveEventInvitation`, frontend `Live/` views
4. **Pusher channels** — finish per-company channel wiring started in Phase 0

**Exit criteria for Phase 5:** a live event can be scheduled, broadcast, and joined; product/order changes propagate in real time.

---

## Phase 6 — Sales & marketing

1. **Notifications domain UI** — `NotificationController`, `FirebaseCloudMessageController`, frontend `Notification/` views (service was ported in Phase 0; this is the management UI)
2. **Campaigns + Promotions** — `CampaignController`, `CampaignFilterController`, `CampaignLineController`, `PromotionFilterController`, `PromotionRepository`, `PromotionItemRepository`, `BuyXYRepository`
3. **PDF Catalogue** — `PdfCatalogueInvitesController`, `PdfCatalogueRepository`, job `SendPdfCatalogueInvitation`, GCS-backed PDF service, frontend `PdfCatalogue/` views
4. **Announcements** — `AnnouncementController`, `AnnouncementRepository`
5. **Leads** — `LeadController`, `LeadRepository`
6. **Sales planner / budgets** — `BudgetController`, `SalesRepBudgetController`, frontend `SalesPlanner/`, `SalesRepBudget/`

**Exit criteria for Phase 6:** a campaign can be built, scoped to customer/item filters, broadcast via FCM/email, and tracked through PDF catalogue + announcements.

---

## Phase 7 — Tail / supporting

1. **Customer depth** — `CustomerVisitController`, `CustomerVisitQuestionController`, `NewCustomerSignupController`, `CustomerSignupFormController`, address management
2. **Flags / feature toggles** — `FlagController`
3. **Data filters UI** — `DataFilterController` (service exists; build out UI)
4. **Data import** — `ImportDataFromFileJobController`, jobs `ImportDataFromFileJobWithMapping`, `ImportDataFromFileJobForWebShopUser`
5. **Retailers / B2B users** — retailer routes group
6. **Developer tools** — `HomeController` admin endpoints (`/cache-clear`, `/logs`, `/page-data-migrate`), `GoogleCloudBuildController`, `LanguageController`
7. **OnePlatform OTP / OnePlatformUser** — only if still in use; otherwise drop

**Exit criteria for Phase 7:** feature parity with old project. Cutover possible.

---

## Cross-cutting verification approach

Every phase should be verified against three loops, in this order:

1. **Backend integration tests** — for each ported repository/controller, port the matching test from `tests/Unit/` or `tests/Feature/` in the old project, or write a fresh one if missing. Run with `./vendor/bin/phpunit` in the new project.
2. **Manual smoke test against a real company DB** — given the multi-tenant DB middleware, every feature must be tested with at least one real company context, not the default DB. Document the test company in the phase's spec.
3. **Frontend feature walkthrough** — `npm run dev` in the new project, log in, exercise the feature's golden path and at least one error path. UI changes are not done until they've been clicked through in a browser.

For Phase 4 (integrations) specifically: each connector needs a sandbox/test credential set documented in the phase spec before code is written. Don't start the port without sandbox access.

---

## Critical files to consult during planning of each phase

| Need | File |
|---|---|
| Domain controller list (old) | `app/Http/Controllers/` (131 files) |
| Repository binding map (old) | `app/Providers/EloquentRepositoryServiceProvider.php` |
| Repository binding map (new) | `app/Providers/RepositoryServiceProvider.php` |
| Old route surface | `routes/web_api.php` (73KB), `routes/api.php`, `routes/api_unthrottled.php`, `routes/web.php` |
| New route surface | `routes/api.php`, `routes/web.php` (verify what's split) |
| Old store modules | `resources/js/store/store.js` |
| New store modules | `resources/js/stores/` |
| Old Vue routes | `resources/js/route.js` |
| New Vue routes | `resources/js/router/index.js` |
| Helper bundle (old) | `app/Helpers/` |
| Multi-tenant middleware (new) | `app/Http/Middleware/SetCompanyDatabaseConnection.php` |
| API response transformer (new) | `app/Transformer/ApiResponseTransformer.php` |

---

## Recommended next step

Pick Phase 0 and produce a detailed, file-level implementation plan against it specifically. Each subsequent phase repeats the same pattern: a focused planning session, then implementation.
