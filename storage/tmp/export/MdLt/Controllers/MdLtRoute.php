<?php
namespace Lt\Modules\MdLt\Controllers;


//this will be general router or default router files
    
    
    LtRoute::post   ('/ai-chat',                        'mdLt@AiChatController@sendMessage')->name('admin-ai.send-message');
 


    LtRoute::get   ('/site-info',                       'mdLt@TbSiteController@getSite')->name('admin-site.getSiteInfo');
    LtRoute::patch ('/site-info/{sn}',                  'mdLt@TbSiteController@updateSiteInfo')->name('admin-site.update-site-info');
    
    LtRoute::get   ('/software/update',                 'mdLt@TbSoftwareUpdateController@index')->name('admin-software-update.index');
    LtRoute::post  ('/software/update/load-code',       'mdLt@TbSoftwareUpdateController@loadCode')->name('admin-software-update.load-code');
    LtRoute::post  ('/software/update/safe-code',       'mdLt@TbSoftwareUpdateController@safeCode')->name('admin-software-update.safe-code');
    LtRoute::post  ('/software/update/status',          'mdLt@TbSoftwareUpdateController@updateStatus')->name('admin-software-update.update-status');
    LtRoute::post  ('/software/update/check',           'mdLt@TbSoftwareUpdateController@checkUpdate')->name('admin-software-update.check-update');
    LtRoute::get  ('/software/update/check-all',       'mdLt@TbSoftwareUpdateController@checkAllUpdate')->name('admin-software-update.check-all-update');
    LtRoute::get  ('/software/backend/permission',       'mdLt@TbSoftwareUpdateController@backendPermision')->name('admin-software.backend-permission');
    LtRoute::patch ('/software/{contentName}/roles/{roleId}/{publishValue}',   'mdLt@TbSoftwareUpdateController@toggleRole')->name('admin-software.toggle-role-publishing');
    
    LtRoute::get ('/dashboard/analysis',                         'mdLt@dashboardController@dashboardIndex')->name('admin-dashboard.dashboard-analysis');
    
    LtRoute::post  ('/software/update/load',            'mdLt@TbSoftwareUpdateController@loadUpdate')->name('admin-software-update.load-update');
    LtRoute::patch   ('/software/{contentName}/update', 'mdLt@TbSoftwareUpdateController@update')->name('admin-software-update.update');
    
       
       
    //////////////////////////////////  ROLE   ///////////////////////////////////////////
    LtRoute::get   ('/roles',                           'mdLt@TbRoleController@index')->name('admin-roles.index');
    LtRoute::get   ('/roles/{ltId}',                    'mdLt@TbRoleController@show')->name('admin-roles.show');
    LtRoute::post  ('/roles',                           'mdLt@TbRoleController@store')->name('admin-roles.store');
    LtRoute::patch ('/roles/{ltId}',                    'mdLt@TbRoleController@update')->name('admin-roles.update');
    LtRoute::patch ('/roles/{ltId}/toggle-enabled',     'mdLt@TbRoleController@toggleEnabled')->name('admin-roles.toggle-enabled');
    LtRoute::patch ('/roles/{ltId}/toggle-protected',   'mdLt@TbRoleController@toggleProtected')->name('admin-roles.toggle-protected');
    LtRoute::delete('/roles/{ltId}',                    'mdLt@TbRoleController@destroy')->name('admin-roles.destroy');

    LtRoute::get('/roles/select',                       'mdLt@TbRoleController@select')->name('admin-roles.select');
    LtRoute::post('/roles/permissions',                 'mdLt@TbRoleController@rolePermissions')->name('admin-roles.role-permissions');


        //////////////////////////////////  USER   //////////////////////////////////////////////////////
    
    
    LtRoute::post   ('/users/logout',                   'mdLt@TbUserController@logout')->name('admin-users.logout');
    LtRoute::get   ('/users',                           'mdLt@TbUserController@index')->name('admin-users.index');
    LtRoute::get   ('/users/{ltId}',                    'mdLt@TbUserController@show')->name('admin-users.show');
    LtRoute::post  ('/users/login',                     'mdLt@TbUserController@login')->name('admin-users.login');
    LtRoute::post  ('/users',                           'mdLt@TbUserController@store')->name('admin-users.store');
    LtRoute::patch ('/users/{ltId}',                    'mdLt@TbUserController@update')->name('admin-users.update');
    LtRoute::patch ('/users/{ltId}/reset-password',     'mdLt@TbUserController@resetPassword')->name('admin-reset.password');
    LtRoute::patch ('/users/{ltId}/change-password',    'mdLt@TbUserController@changePassword')->name('admin-change.password');
    LtRoute::delete('/users/{ltId}',                    'mdLt@TbUserController@destroy')->name('admin-users.destroy');
    LtRoute::patch ('/users/{ltId}/toggle-enabled',     'mdLt@TbUserController@toggleEnabled')->name('admin-users.toggle-enabled');
    LtRoute::post ('/users/request-password-reset',     'mdLt@TbUserController@requestPasswordReset')->name('admin-users.request-password-reset');
    LtRoute::post ('/users/verify-otp',                 'mdLt@TbUserController@verifyOtp')->name('admin-users.verify-otp');
    LtRoute::post ('/users/reset-password',            'mdLt@TbUserController@reset')->name('admin-reset.password-token');
    
    
    //////////////////////////////////  MODULE   //////////////////////////////////////////////////////

    LtRoute::get   ('/packages',                   'mdLt@TbPackageController@index')->name('admin-packages.index');
    LtRoute::post  ('/packages',                   'mdLt@TbPackageController@store')->name('admin-packages.store');
    LtRoute::patch ('/packages/{ltId}',            'mdLt@TbPackageController@update')->name('admin-packages.update');
    LtRoute::delete('/packages/{ltId}',            'mdLt@TbPackageController@destroy')->name('admin-packages.destroy');
    LtRoute::patch ('/packages/{ltId}/toggle-published',   'mdLt@TbPackageController@togglePublished')->name('admin-packages.toggle-published');
    LtRoute::get ('/packages/export-tables',     'mdLt@TbPackageController@exportTables')->name('admin-packages.export-tables');
    LtRoute::post ('/packages/exports',     'mdLt@TbPackageController@exports')->name('admin-packages.export');
    LtRoute::post ('/packages/imports',     'mdLt@TbPackageController@imports')->name('admin-packages.imports');
    LtRoute::patch ('/packages/{ltId}/toggle-default',     'mdLt@TbPackageController@toggleDefault')->name('admin-packages.toggle-default');
    
    //////////////////////////////////  MODULE CONTENT  //////////////////////////////////////////////////////

    LtRoute::get   ('/contents',            'mdLt@TbContentController@show')->name('admin-contents.show');
    LtRoute::post  ('/contents',  'mdLt@TbContentController@store')->name('admin-contents.store');
    LtRoute::patch ('/contents/{ltId}',            'mdLt@TbContentController@update')->name('admin-contents.update');
    LtRoute::delete('/contents/{ltId}',            'mdLt@TbContentController@destroy')->name('admin-contents.destroy');
    LtRoute::get ('/contents/{ltId}/published-pages',   'mdLt@TbContentController@publishedPages')->name('admin-contents.published-pages');
    LtRoute::patch ('/contents/{ltId}/roles/{roleId}/{publishValue}',   'mdLt@TbContentController@toggleRole')->name('admin-contents.toggle-role-publishing');
    
     LtRoute::post ('/contents/edit/load-code',   function(){ echo LtDdm::loadEditCode();})->name('admin-contents.load-edit-code');
     LtRoute::post ('/contents/edit/save-code',   function(){ echo LtDdm::saveEditCode();})->name('admin-contents.save-edit-code');
     LtRoute::patch ('/contents/move-contents',   'mdLt@TbContentController@moveContents')->name('admin-contents.move-content');
     LtRoute::post ('/contents/download',         'mdLt@TbContentController@downloadContent')->name('admin-contents.download-content');
     LtRoute::post ('/contents/upload',           'mdLt@TbContentController@uploadContent')->name('admin-contents.upload-content');
     
     LtRoute::post ('/contents/auto-create',      'mdLt@TbContentController@autoCreate')->name('admin-contents.auto-create');
   
    
    LtRoute::post ('/page-contents/toggle-published',   'mdLt@TbPageContentController@togglePublished')->name('admin-page-contents.toggle-published');
   
   
    
    

    //////////////////////////////////  MENU   //////////////////////////////////////////////////////

    LtRoute::get   ('/menus',                   'mdLt@TbMenuController@index')->name('admin-menu.index');
    LtRoute::post  ('/menus',                   'mdLt@TbMenuController@store')->name('admin-menu.store');
    LtRoute::patch ('/menus/{ltId}',            'mdLt@TbMenuController@update')->name('admin-menu.update');
    LtRoute::delete('/menus/{ltId}',            'mdLt@TbMenuController@destroy')->name('admin-menu.destroy');
    LtRoute::patch ('/menus/{ltId}/toggle-quicklink',  'mdLt@TbMenuController@toggleQuicklink')->name('admin-menu.toggle-quicklink');
    LtRoute::patch ('/menus/{ltId}/toggle-service',    'mdLt@TbMenuController@toggleService')->name('admin-menu.toggle-service');
    LtRoute::get ('/menus/parents',    'mdLt@TbMenuController@parents')->name('admin-menu.parents');
    LtRoute::patch ('/menus/{ltId}/roles/{roleId}/{publishValue}',   'mdLt@TbMenuController@toggleRole')->name('admin-menu.toggle-role-publishing');
    
    LtRoute::get   ('/menus/is-quicklink',            'mdLt@TbMenuController@isQuicklink')->name('admin-menu.is_quicklink');
    LtRoute::get   ('/menus/is-service',              'mdLt@TbMenuController@isService')->name('admin-menu.is_service');
    LtRoute::get   ('/menus/is-navigation',              'mdLt@TbMenuController@isNavigation')->name('admin-menu.is_navigation');
    
    //////////////////////////////////  PAGE   //////////////////////////////////////////////////////
    
    LtRoute::get   ('/pages',                   'mdLt@TbPageController@index')->name('admin-pages.index');
    LtRoute::get   ('/pages/{ltId}',            'mdLt@TbPageController@show')->name('admin-pages.show');
    LtRoute::post  ('/pages',                   'mdLt@TbPageController@store')->name('admin-pages.store');
    LtRoute::patch ('/pages/{ltId}',            'mdLt@TbPageController@update')->name('admin-pages.update');
    LtRoute::delete('/pages/{ltId}',            'mdLt@TbPageController@destroy')->name('admin-pages.destroy');
    LtRoute::get   ('/pages/{ltId}/published-contents',   'mdLt@TbPageController@publishedContents')->name('admin-pages.published-contents');
    LtRoute::patch ('/pages/{ltId}/roles/{roleId}/{publishValue}',   'mdLt@TbPageController@toggleRole')->name('admin-pages.toggle-role-publishing');
    LtRoute::get   ('/pages/{ltId}/published-region',   'mdLt@TbPageController@publishedRegion')->name('admin-pages.published-region');
    
     LtRoute::patch ('/pages/published/move-contents',   'mdLt@TbPageController@moveContents')->name('admin-pages.move-contents');
     LtRoute::patch ('/pages/published/move-region',   'mdLt@TbPageController@moveRegion')->name('admin-pages.move-region');
     LtRoute::patch ('/pages/{ltId}/route-path',   'mdLt@TbPageController@routePath')->name('admin-pages.route_path');
     LtRoute::patch ('/pages/{ltId}/toggle-published',   'mdLt@TbPageController@togglePublished')->name('admin-pages.toggle-published');
     
     LtRoute::post ('/pages/toggle-route',   'mdLt@TbPageController@toggleRoute')->name('admin-pages.toggle-route');
     LtRoute::get   ('/pages/{ltId}/published-route',   'mdLt@TbPageController@publishedRoute')->name('admin-pages.published-route');
     
            
      LtRoute::post ('/pages/published/region',   'mdLt@TbPageController@publishToRegion')->name('admin-pages.publish-to-region');
     
    ////////////////////////////////////  FILE ////////////////////////////////////////////////////////
    
     LtRoute::get    ('/files',                      'mdLt@TbFileManagementController@index')->name('admin-files.index');
     LtRoute::delete ('/files',                      'mdLt@TbFileManagementController@deleteItems')->name('admin-files.delete');
     LtRoute::get    ('/files/navigate-to-folder',   'mdLt@TbFileManagementController@navigateToFolder')->name('admin-navigate-to-folder');
     LtRoute::post   ('/files/create-items',         'mdLt@TbFileManagementController@createItems')->name('admin-create-items');
     LtRoute::post   ('/files/upload',              'mdLt@TbFileManagementController@uploads')->name('admin-uploads');
     LtRoute::post   ('/files/download',             'mdLt@TbFileManagementController@download')->name('admin-download');
     LtRoute::get    ('/files/read-file',            'mdLt@TbFileManagementController@readFile')->name('admin-read-file');
     LtRoute::post   ('/files/contents/edit',        'mdLt@TbFileManagementController@editFileContent')->name('admin-content-edit');
     LtRoute::post   ('/files/rename',               'mdLt@TbFileManagementController@rename')->name('admin-rename');
     
     
     LtRoute::get   ('/responses',                  'mdLt@TbResponseController@index')->name('admin-index');
     LtRoute::post  ('/responses',                  'mdLt@TbResponseController@store')->name('admin-response-store');
     LtRoute::patch ('/responses/{ltId}',           'mdLt@TbResponseController@update')->name('admin-update');
     LtRoute::delete ('/responses/{ltId}',          'mdLt@TbResponseController@destroy')->name('admin-destroy');
     LtRoute::patch ('/responses/{ltId}/toggle-enabled', 'mdLt@TbResponseController@toggleIsEnabled')->name('admin-toggle-enabled');
     
     
     LtRoute::get   ('/tables',                      'mdLt@tableManagementController@index')->name('admin-tables.index');
     LtRoute::post  ('/tables',                      'mdLt@tableManagementController@store')->name('admin-tables.store');
     LtRoute::get   ('/tables/{tableName}/browse',   'mdLt@tableManagementController@browse')->name('admin-tables.browse');
     LtRoute::get   ('/tables/{tableName}/structure',   'mdLt@tableManagementController@structure')->name('admin-tables.structure');
     LtRoute::patch ('/tables/{tableName}/truncate',   'mdLt@tableManagementController@truncate')->name('admin-tables.truncate');
     LtRoute::delete ('/tables/{tableName}/drop',   'mdLt@tableManagementController@drop')->name('admin-tables.drop');
     LtRoute::delete ('/tables/delete-record',      'mdLt@tableManagementController@deleteRecord')->name('admin-tables.delete-record');
     LtRoute::POST ('/tables/sql/execute',      'mdLt@tableManagementController@sqlExecute')->name('admin-tables.sql-execute');

