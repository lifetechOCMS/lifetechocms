<?php
namespace Lt\Modules\MdLt\Controllers;


//this will be general router or default router files
    
    
    LtRoute::post   ('/ai-chat',                        'MdLt@AiChatController@sendMessage')->name('admin-ai.send-message');
 


    LtRoute::get   ('/site-info',                       'MdLt@TbSiteController@getSite')->name('admin-site.getSiteInfo');
    LtRoute::patch ('/site-info/{sn}',                  'MdLt@TbSiteController@updateSiteInfo')->name('admin-site.update-site-info');
    
    LtRoute::get   ('/software/update',                 'MdLt@TbSoftwareUpdateController@index')->name('admin-software-update.index');
    LtRoute::post  ('/software/update/load-code',       'MdLt@TbSoftwareUpdateController@loadCode')->name('admin-software-update.load-code');
    LtRoute::post  ('/software/update/safe-code',       'MdLt@TbSoftwareUpdateController@safeCode')->name('admin-software-update.safe-code');
    LtRoute::post  ('/software/update/status',          'MdLt@TbSoftwareUpdateController@updateStatus')->name('admin-software-update.update-status');
    LtRoute::post  ('/software/update/check',           'MdLt@TbSoftwareUpdateController@checkUpdate')->name('admin-software-update.check-update');
    LtRoute::get  ('/software/update/check-all',       'MdLt@TbSoftwareUpdateController@checkAllUpdate')->name('admin-software-update.check-all-update');
    LtRoute::get  ('/software/backend/permission',       'MdLt@TbSoftwareUpdateController@backendPermision')->name('admin-software.backend-permission');
    LtRoute::patch ('/software/{contentName}/roles/{roleId}/{publishValue}',   'MdLt@TbSoftwareUpdateController@toggleRole')->name('admin-software.toggle-role-publishing');
    
    LtRoute::get ('/dashboard/analysis',                         'MdLt@dashboardController@dashboardIndex')->name('admin-dashboard.dashboard-analysis');
    
    LtRoute::post  ('/software/update/load',            'MdLt@TbSoftwareUpdateController@loadUpdate')->name('admin-software-update.load-update');
    
    LtRoute::patch ('/software/{contentName}/update', 'MdLt@TbSoftwareUpdateController@update')->name('admin-software-update.update');
    
       
       
    //////////////////////////////////  ROLE   ///////////////////////////////////////////
    LtRoute::get   ('/roles',                           'MdLt@TbRoleController@index')->name('admin-roles.index');
    LtRoute::get   ('/roles/{ltId}',                    'MdLt@TbRoleController@show')->name('admin-roles.show');
    LtRoute::post  ('/roles',                           'MdLt@TbRoleController@store')->name('admin-roles.store');
    LtRoute::patch ('/roles/{ltId}',                    'MdLt@TbRoleController@update')->name('admin-roles.update');
    LtRoute::patch ('/roles/{ltId}/toggle-enabled',     'MdLt@TbRoleController@toggleEnabled')->name('admin-roles.toggle-enabled');
    LtRoute::patch ('/roles/{ltId}/toggle-protected',   'MdLt@TbRoleController@toggleProtected')->name('admin-roles.toggle-protected');
    LtRoute::delete('/roles/{ltId}',                    'MdLt@TbRoleController@destroy')->name('admin-roles.destroy');

    LtRoute::get('/roles/select',                       'MdLt@TbRoleController@select')->name('admin-roles.select');
    LtRoute::post('/roles/permissions',                 'MdLt@TbRoleController@rolePermissions')->name('admin-roles.role-permissions');


        //////////////////////////////////  USER   //////////////////////////////////////////////////////
    
    LtRoute::post  ('/users-admin',                     'MdLt@TbUserController@storeAdmin')->name('admin-users.store-admin');
    LtRoute::post   ('/users/logout',                   'MdLt@TbUserController@logout')->name('admin-users.logout');
    LtRoute::get   ('/users',                           'MdLt@TbUserController@index')->name('admin-users.index');
    LtRoute::get   ('/users/{ltId}',                    'MdLt@TbUserController@show')->name('admin-users.show');
    LtRoute::post  ('/users/login',                     'MdLt@TbUserController@login')->name('admin-users.login');
    LtRoute::post  ('/users',                           'MdLt@TbUserController@store')->name('admin-users.store');
    LtRoute::patch ('/users/{ltId}',                    'MdLt@TbUserController@update')->name('admin-users.update');
    LtRoute::patch ('/users/{ltId}/reset-password',     'MdLt@TbUserController@resetPassword')->name('admin-reset.password');
    LtRoute::patch ('/users/{ltId}/change-password',     'MdLt@TbUserController@changePassword')->name('admin-change.password');
    LtRoute::delete('/users/{ltId}',                    'MdLt@TbUserController@destroy')->name('admin-users.destroy');
    LtRoute::patch ('/users/{ltId}/toggle-enabled',     'MdLt@TbUserController@toggleEnabled')->name('admin-users.toggle-enabled');
    
       //forgot password
    LtRoute::post ('/users/request-password-reset',     'mdLt@TbUserController@requestPasswordReset')->name('admin-users.request-password-reset');
    LtRoute::post ('/users/verify-otp',                 'mdLt@TbUserController@verifyOtp')->name('admin-users.verify-otp');
    LtRoute::post ('/users/reset-password',            'mdLt@TbUserController@reset')->name('admin-reset.password-token');
    
    
    //////////////////////////////////  MODULE   //////////////////////////////////////////////////////
    LtRoute::get   ('/packages/filtered',                   'MdLt@TbPackageController@filteredPackages')->name('admin-packages.filtered'); //new

    LtRoute::get   ('/packages',                   'MdLt@TbPackageController@index')->name('admin-packages.index');
    LtRoute::post  ('/packages',                   'MdLt@TbPackageController@store')->name('admin-packages.store');
    LtRoute::patch ('/packages/{ltId}',            'MdLt@TbPackageController@update')->name('admin-packages.update');
    LtRoute::delete('/packages/{ltId}',            'MdLt@TbPackageController@destroy')->name('admin-packages.destroy');
    LtRoute::patch ('/packages/{ltId}/toggle-published',   'MdLt@TbPackageController@togglePublished')->name('admin-packages.toggle-published');
    LtRoute::get ('/packages/export-tables',     'MdLt@TbPackageController@exportTables')->name('admin-packages.export-tables');
    LtRoute::post ('/packages/exports',     'MdLt@TbPackageController@exports')->name('admin-packages.export');
    LtRoute::post ('/packages/imports',     'MdLt@TbPackageController@imports')->name('admin-packages.imports');
    LtRoute::patch ('/packages/{ltId}/toggle-default',     'MdLt@TbPackageController@toggleDefault')->name('admin-packages.toggle-default');
    
    LtRoute::post  ('/packages/validate-dependencies',  'MdLt@TbPackageController@validateDependencies')->name('admin-packages.validate-dependencies');
    LtRoute::get  ('/packages/marketplace/browse',  'MdLt@TbPackageController@browseProducts')->name('admin-packages.browse_products');
    LtRoute::post  ('/packages/marketplace/install',  'MdLt@TbPackageController@installUpdate')->name('admin-packages.install_update');
    //////////////////////////////////  MODULE CONTENT  //////////////////////////////////////////////////////

    LtRoute::get   ('/contents',            'MdLt@TbContentController@show')->name('admin-contents.show');
    LtRoute::post  ('/contents',  'MdLt@TbContentController@store')->name('admin-contents.store');
    LtRoute::patch ('/contents/{ltId}',            'MdLt@TbContentController@update')->name('admin-contents.update');
    LtRoute::delete('/contents/{ltId}',            'MdLt@TbContentController@destroy')->name('admin-contents.destroy');
    LtRoute::get ('/contents/{ltId}/published-pages',   'MdLt@TbContentController@publishedPages')->name('admin-contents.published-pages');
    LtRoute::patch ('/contents/{ltId}/roles/{roleId}/{publishValue}',   'MdLt@TbContentController@toggleRole')->name('admin-contents.toggle-role-publishing');
    LtRoute::patch ('/contents/{ltId}/toggle-published',   'MdLt@TbContentController@togglePublishedContent')->name('admin-packages.toggle-published-content');
     LtRoute::post ('/contents/edit/load-code',   function(){ echo LtDdm::loadEditCode();})->name('admin-contents.load-edit-code');
     LtRoute::post ('/contents/edit/save-code',   function(){ echo LtDdm::saveEditCode();})->name('admin-contents.save-edit-code');
     LtRoute::patch ('/contents/move-contents',   'MdLt@TbContentController@moveContents')->name('admin-contents.move-content');
     LtRoute::post ('/contents/download',         'MdLt@TbContentController@downloadContent')->name('admin-contents.download-content');
     LtRoute::post ('/contents/upload',           'MdLt@TbContentController@uploadContent')->name('admin-contents.upload-content');
     
     LtRoute::post ('/contents/auto-create',      'MdLt@TbContentController@autoCreate')->name('admin-contents.auto-create');
   
    
    LtRoute::post ('/page-contents/toggle-published',   'MdLt@TbPageContentController@togglePublished')->name('admin-page-contents.toggle-published');
   
     LtRoute::get ('/contents/get-folders',   'MdLt@TbContentController@getFolders')->name('admin-contents.getFolders'); //
     
     LtRoute::get ('/contents/{packageName}/fetch-layouts',   'MdLt@TbContentController@fetchLayouts')->name('admin-contents.fetch-Layouts'); //new
     LtRoute::post ('/contents/assign-layout',   'MdLt@TbContentController@assignLayouts')->name('admin-contents.assign-Layouts'); //new
   
     LtRoute::post ('/contents/synchronize',           'MdLt@TbContentController@synchronizeContent')->name('admin-contents.synchronize-content');
     LtRoute::post ('/contents/scan-to-file',                  'MdLt@TbContentController@scanContent')->name('admin-contents.scan-content');
     LtRoute::get ('/contents/regions',                  'MdLt@TbContentController@contentRegions')->name('admin-contents.content-regions');
    
    

    //////////////////////////////////  MENU   //////////////////////////////////////////////////////

    LtRoute::get   ('/menus',                   'MdLt@TbMenuController@index')->name('admin-menu.index');
    LtRoute::post  ('/menus',                   'MdLt@TbMenuController@store')->name('admin-menu.store');
    LtRoute::patch ('/menus/{ltId}',            'MdLt@TbMenuController@update')->name('admin-menu.update');
    LtRoute::delete('/menus/{ltId}',            'MdLt@TbMenuController@destroy')->name('admin-menu.destroy');
    LtRoute::patch ('/menus/{ltId}/toggle-quicklink',  'MdLt@TbMenuController@toggleQuicklink')->name('admin-menu.toggle-quicklink');
    LtRoute::patch ('/menus/{ltId}/toggle-service',    'MdLt@TbMenuController@toggleService')->name('admin-menu.toggle-service');
    LtRoute::get ('/menus/parents',    'MdLt@TbMenuController@parents')->name('admin-menu.parents');
    LtRoute::patch ('/menus/{ltId}/roles/{roleId}/{publishValue}',   'MdLt@TbMenuController@toggleRole')->name('admin-menu.toggle-role-publishing');
    
    LtRoute::get   ('/menus/is-quicklink',            'MdLt@TbMenuController@isQuicklink')->name('admin-menu.is_quicklink');
    LtRoute::get   ('/menus/is-service',              'MdLt@TbMenuController@isService')->name('admin-menu.is_service');
    LtRoute::get   ('/menus/is-navigation',              'MdLt@TbMenuController@isNavigation')->name('admin-menu.is_navigation');
    
    LtRoute::patch ('/menus/{ltId}/toggle-published',   'MdLt@TbMenuController@togglePublishedMenu')->name('admin-packages.toggle-published-menu');
    
    //////////////////////////////////  PAGE   //////////////////////////////////////////////////////
    
    LtRoute::get   ('/pages',                   'MdLt@TbPageController@index')->name('admin-pages.index');
    LtRoute::get   ('/pages/{ltId}',            'MdLt@TbPageController@show')->name('admin-pages.show');
    LtRoute::post  ('/pages',                   'MdLt@TbPageController@store')->name('admin-pages.store');
    LtRoute::patch ('/pages/{ltId}',            'MdLt@TbPageController@update')->name('admin-pages.update');
    LtRoute::delete('/pages/{ltId}',            'MdLt@TbPageController@destroy')->name('admin-pages.destroy');
    LtRoute::get   ('/pages/{ltId}/published-contents',   'MdLt@TbPageController@publishedContents')->name('admin-pages.published-contents');
    LtRoute::patch ('/pages/{ltId}/roles/{roleId}/{publishValue}',   'MdLt@TbPageController@toggleRole')->name('admin-pages.toggle-role-publishing');
    LtRoute::get   ('/pages/{ltId}/published-region',   'MdLt@TbPageController@publishedRegion')->name('admin-pages.published-region');
    
     LtRoute::patch ('/pages/published/move-contents',   'MdLt@TbPageController@moveContents')->name('admin-pages.move-contents');
     LtRoute::patch ('/pages/published/move-region',   'MdLt@TbPageController@moveRegion')->name('admin-pages.move-region');
     LtRoute::patch ('/pages/{ltId}/route-path',   'MdLt@TbPageController@routePath')->name('admin-pages.route_path');
     LtRoute::patch ('/pages/{ltId}/toggle-published',   'MdLt@TbPageController@togglePublished')->name('admin-pages.toggle-published');
     
     LtRoute::post ('/pages/toggle-route',   'MdLt@TbPageController@toggleRoute')->name('admin-pages.toggle-route');
     LtRoute::get   ('/pages/{ltId}/published-route',   'MdLt@TbPageController@publishedRoute')->name('admin-pages.published-route');
     
            
      LtRoute::post ('/pages/published/region',   'MdLt@TbPageController@publishToRegion')->name('admin-pages.publish-to-region');
     
    ////////////////////////////////////  FILE ////////////////////////////////////////////////////////
    
     LtRoute::get    ('/files',                      'MdLt@TbFileManagementController@index')->name('admin-files.index');
     LtRoute::delete ('/files',                      'MdLt@TbFileManagementController@deleteItems')->name('admin-files.delete');
     LtRoute::get    ('/files/navigate-to-folder',   'MdLt@TbFileManagementController@navigateToFolder')->name('admin-navigate-to-folder');
     LtRoute::post   ('/files/create-items',         'MdLt@TbFileManagementController@createItems')->name('admin-create-items');
     LtRoute::post   ('/files/upload',              'MdLt@TbFileManagementController@uploads')->name('admin-uploads');
     LtRoute::post   ('/files/download',             'MdLt@TbFileManagementController@download')->name('admin-download');
     LtRoute::get    ('/files/read-file',            'MdLt@TbFileManagementController@readFile')->name('admin-read-file');
     LtRoute::post   ('/files/contents/edit',        'MdLt@TbFileManagementController@editFileContent')->name('admin-content-edit');
     LtRoute::post   ('/files/rename',               'MdLt@TbFileManagementController@rename')->name('admin-rename');
     
     
     LtRoute::get   ('/responses',                  'MdLt@TbResponseController@index')->name('admin-index');
     LtRoute::post  ('/responses',                  'MdLt@TbResponseController@store')->name('admin-response-store');
     LtRoute::patch ('/responses/{ltId}',           'MdLt@TbResponseController@update')->name('admin-update');
     LtRoute::delete ('/responses/{ltId}',          'MdLt@TbResponseController@destroy')->name('admin-destroy');
     LtRoute::patch ('/responses/{ltId}/toggle-enabled', 'MdLt@TbResponseController@toggleIsEnabled')->name('admin-toggle-enabled');
     
     
     LtRoute::get   ('/tables',                      'MdLt@tableManagementController@index')->name('admin-tables.index');
     LtRoute::post  ('/tables',                      'MdLt@tableManagementController@store')->name('admin-tables.store');
     LtRoute::get   ('/tables/{tableName}/browse',   'MdLt@tableManagementController@browse')->name('admin-tables.browse');
     LtRoute::get   ('/tables/{tableName}/structure',   'MdLt@tableManagementController@structure')->name('admin-tables.structure');
     LtRoute::patch ('/tables/{tableName}/truncate',   'MdLt@tableManagementController@truncate')->name('admin-tables.truncate');
     LtRoute::delete ('/tables/{tableName}/drop',   'MdLt@tableManagementController@drop')->name('admin-tables.drop');
     LtRoute::delete ('/tables/delete-record',      'MdLt@tableManagementController@deleteRecord')->name('admin-tables.delete-record');
     LtRoute::POST ('/tables/sql/execute',      'MdLt@tableManagementController@sqlExecute')->name('admin-tables.sql-execute');

