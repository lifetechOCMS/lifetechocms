<?php

LtRoute::get('/mail/test', 'PlgMail@PlgMailController@test');
LtRoute::get('/mail/send', 'PlgMail@PlgMailController@send');



LtRoute::get('/queues','PlgMail@TbMailQueueController@index')->name('admin-queue.index');
LtRoute::post('/queues/send-mail','PlgMail@TbMailQueueController@sendMail')->name('admin-queue.sendMail');
LtRoute::delete('/queues/{ltId}','PlgMail@TbMailQueueController@destroy')->name('admin-queue.destroy');
LtRoute::post('/queues','PlgMail@OgitechPluginMail@store')->name('admin-queue.store');
LtRoute::post('/queues/send-multiple-mail','PlgMail@TbMailQueueController@sendMultipleMail')->name('admin-queue.send_multiple_mail');


?>
