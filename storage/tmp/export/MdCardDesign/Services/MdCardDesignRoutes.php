<?php

/* ===== CARD SECTIONS (tb_card_section) ===== */

LtRoute::get('/cards/sections','MdCardDesign@TbCardSectionController@index')->name('admin-cards.sections.index');
LtRoute::post('/cards/sections','MdCardDesign@TbCardSectionController@store')->name('admin-cards.sections.store');
LtRoute::get('/cards/sections/{ltId}','MdCardDesign@TbCardSectionController@show')->name('admin-cards.sections.show');
LtRoute::get('/cards/sections/{ltId}/edit','MdCardDesign@TbCardSectionController@edit')->name('admin-cards.sections.edit');
LtRoute::patch('/cards/sections/{ltId}','MdCardDesign@TbCardSectionController@update')->name('admin-cards.sections.update');
LtRoute::patch('/cards/sections/{ltId}/toggle-enabled','MdCardDesign@TbCardSectionController@toggleEnabled')->name('admin-cards.sections.toggle_enabled');
LtRoute::delete('/cards/sections/{ltId}','MdCardDesign@TbCardSectionController@destroy')->name('admin-cards.sections.delete');

/* ===== CARD CONTENT UI (tb_card_content_layout) ===== */
LtRoute::get('/cards/contents/layouts','MdCardDesign@TbCardContentLayoutController@index')->name('admin-cards.content_layout.index');
LtRoute::post('/cards/contents/layouts','MdCardDesign@TbCardContentLayoutController@store')->name('admin-cards.content_layout.store');
LtRoute::patch('/cards/contents/layouts/{ltId}/assign-section/{sectionId}','MdCardDesign@TbCardContentLayoutController@assignSection')->name('admin-cards.content_layout.assign_section');
LtRoute::patch('/cards/contents/layouts/{ltId}','MdCardDesign@TbCardContentLayoutController@update')->name('admin-cards.content_layout.update');
LtRoute::patch('/cards/contents/layouts/{ltId}/toggle-enabled','MdCardDesign@TbCardContentLayoutController@toggleEnabled')->name('admin-cards.content_layout.toggle_enabled');
LtRoute::delete('/cards/contents/layouts/{ltId}','MdCardDesign@TbCardContentLayoutController@destroy')->name('admin-cards.content_layout.delete');
LtRoute::get('/content/cards/{contentName}','MdCardDesign@TbCardContentLayoutController@resolveDefault')->name('cards.content_layout.resolve_default');
LtRoute::get('/content/cards/{packageName}/{contentName}','MdCardDesign@TbCardContentLayoutController@resolvePackage')->name('cards.content_layout.resolve_pkg');


/* ===== CARD PROFILES (tb_card_profile) ===== */
LtRoute::get('/cards/profiles','MdCardDesign@TbCardProfileController@index')->name('admin-cards.profiles.index');
LtRoute::post('/cards/profiles','MdCardDesign@TbCardProfileController@store')->name('admin-cards.profiles.store');
LtRoute::get('/cards/profiles/{ltId}','MdCardDesign@TbCardProfileController@show')->name('admin-cards.profiles.show');
LtRoute::get('/cards/profiles/{ltId}/edit','MdCardDesign@TbCardProfileController@edit')->name('admin-cards.profiles.edit');
LtRoute::patch('/cards/profiles/{ltId}','MdCardDesign@TbCardProfileController@update')->name('admin-cards.profiles.update');
LtRoute::patch('/cards/profiles/{ltId}/toggle-enabled','MdCardDesign@TbCardProfileController@toggleEnabled')->name('admin-cards.profiles.toggle_enabled');
LtRoute::patch('/cards/profiles/{ltId}/toggle-featured','MdCardDesign@TbCardProfileController@toggleFeatured')->name('admin-cards.profiles.toggle_featured');
LtRoute::delete('/cards/profiles/{ltId}','MdCardDesign@TbCardProfileController@destroy')->name('admin-cards.profiles.delete');

/* ===== SECTION ↔ PROFILE MEMBERSHIP (tb_card_section_profile) ===== */
LtRoute::get('/cards/sections/profiles','MdCardDesign@TbCardSectionProfileController@index')->name('admin-cards.section_profiles.index');
LtRoute::post('/cards/sections/profiles','MdCardDesign@TbCardSectionProfileController@attach')->name('admin-cards.section_profiles.attach');
LtRoute::put('/cards/sections/{sectionId}/profiles/{mapId}','MdCardDesign@TbCardSectionProfileController@update')->name('admin-cards.section_profiles.update');
LtRoute::patch('/cards/sections/{sectionId}/profiles/{mapId}/toggle-enabled','MdCardDesign@TbCardSectionProfileController@toggleEnabled')->name('admin-cards.section_profiles.toggle_enabled');
LtRoute::delete('/cards/sections/profiles/{mapId}','MdCardDesign@TbCardSectionProfileController@detach')->name('admin-cards.section_profiles.detach');
LtRoute::patch('/cards/sections/{sectionId}/profiles/reorder','MdCardDesign@TbCardSectionProfileController@reorder')->name('admin-cards.section_profiles.reorder');

/* ===== OPTIONAL: Render cards directly ===== */
LtRoute::get('/render/cards/{packageName}/{contentName}','MdCardDesign@TbCardContentLayoutController@renderPackage')->name('cards.render.package');

