# A Forms API Library for PHP

## Important notices

This project is a fork from : https://github.com/darrenmothersele/php-forms-api

The main repository for this code is github. If you have received the file
from another location please check this URL for the latest version:

    https://github.com/degami/php-forms-api

For bug reports, feature requests, or support requests, please start a case
on the GitHub case tracker:

    https://github.com/degami/php-forms-api/issues

This library is open source licenses using GPL. See LICENSE.txt for more info.

Requirements:

  * PHP 5.2.5+
  * jQuery UI
  * jQuery-mask-plugin ( https://github.com/igorescobar/jQuery-Mask-Plugin component is not included as a dependency in composer.json as the packagist.org package info is broken, you need do download the file by yourself )
  * Google's reCaptcha lib in order to use recaptcha fields ( https://code.google.com/p/recaptcha/downloads/list?q=label:phplib-Latest )

## Roadmap

 * **Finish the Documentation!**
 * ~~Finish implementation of options such as _disabled_, _attributes_,~~
 * Offer a wider range of examples
 * Complete the javascript functionality for ~~collapsible fieldsets and~~ the
   password strength meter for password fields.
 * Complete unit tests.
 * AJAX support for ~~field submission~~ and file uploads.
 * Provide a better default theme and a methodology for defining themes.
 * ~~Implement new field types such as button, dates~~
 * ~~Add support for masked fields, possibly using a jquery plugin.~~
 * ~~Multistep forms~~

## Introduction

This Forms API for PHP takes all the hard work out of building, validating, and
processing HTML forms in your PHP applications. The Forms API handles all
common form elements and validation rules, and it's object-oriented design
can be easily expanded with new fields and validation rules.

## Basic forms workflow

The basic workflow of this forms API is as follows:

 * Create new form object
 * Add fields to form
 * Process form
 * Display form

It may not be obvious at first why you process the form before you display the
form. This is done because, when using this library, usually the same page
(or controller in MVC) will handle the form submissions as well as displaying
the form. This is useful because if the form fails validation you are in the
right place to display the form again with error messages and prompts to
correct mistakes and resubmit the form.

Let's look at the steps of the form workflow in more detail:

### Create new form object

The form object is the main starting point for using this Forms API. When you
create a form you can provide some options in an array to override the default
options. By default the form submits back to itself, this is the most useful
configuration because you can use the same object to validate and process the
submitted form.

NB: You can use the default form ID of cs_form if you only have one form, but
if you have multiple forms then you must override this value. The form ID is
used to generate the HTML ID tag, so must be unique for valid HTML. It is also
used in generating the default name of the php function to use when the form is
submitted.

### Add fields to your form

You can add any number of fields to a form, but the only required field is a
submit button so that the user can submit the form.

You can nest fields inside fieldsets if you want to break up longer forms, or
hide advanced options that are not commonly used. Javascript (jQuery) is
provided to support collapsible fieldsets.

### Processing forms

This is what happens when you submit a form for processing:

 * Check the incoming request to see if form has been submitted. If this page
   request is the result of a submission then copy the values from the request
   into the form.
 * Call any alter functions for this form_id.
 * Run any field processors defined to run before validation (preprocess).
 * Validate the form. If the form was not submitted fail validation without
   checking anything so the form displays for the first time. If this request
   was the result of submitting this form run all field validators. If any of
   the validators fail set error conditions in the form. Then the form's level
   validate handler is called if it is found.
 * If the form was submitted and passed validation run any field processors
   defined to run after validation (postprocess). Then check if the submit
   handler is exists as a php function.
 * If the submit handler is found it is called, passing in
   the final form object (which can be used to extract the values).

### Display the form

Displaying the form is actually the final step. If there is no form submission
found in the request then this is the first time it is displayed and it has
been populated with the default values. Alternatively the form may be being
displayed as the result of a request that has failed validation. In this case
extra information is displayed about the error conditions.


## A walkthrough of the contact form example

You will find the source code for this example in this file: example/contact.php



## Form API reference

To use this Form API in your projects you just need to include the main
form.php file as follows:

    require 'form.php';

Be sure to correct the path to form.php if it is not in the same folder as
your script. The static assets (images, css and javascript) should by default
be in a folder called assets. You may need to set the BASE_PATH configuration
option if this changes.

### Form objects

This example array shows all valid options for form objects, and their
default values:

    $options = array(
      'form_id' => 'cs_form',
      'action' => '',
      'attributes' => array(),
      'method' => 'post',
      'container_tag' => FORMS_DEFAULT_FORM_CONTAINER_TAG, //set in configuration
      'container_class' => FORMS_DEFAULT_FORM_CONTAINER_CLASS, //set in configuration
      'prefix' => '',
      'suffix' => '',
      'submit' => array(FORM_ID .'_submit'),
      'validate' => array(FORM_ID .'_validate'),
      'inline_errors' => FALSE,
      'ajax_submit_url' => '',
    );

fields get a form reference during render , so they can modify the form object
state (eg. add the enctype attribute, or js scripts)

### Field objects

Here are the available fields and their options:

#### Common

    $options = array(
      'title' => '',
      'description' => '',
      'name' => '',
      'id' => '',
      'attributes' => array(),
      'default_value' => '',
      'disabled' => FALSE,
      'stop_on_first_error' => FALSE,
      'tooltip' => FALSE,
      'container_tag' => FORMS_DEFAULT_FIELD_CONTAINER_TAG, //set in configuration
      'container_class' => FORMS_DEFAULT_FIELD_CONTAINER_CLASS, //set in configuration
      'label_class' => FORMS_DEFAULT_FIELD_LABEL_CLASS, //set in configuration
      'container_inherits_classes' => FALSE,
      'required_position' => 'after',
      'prefix' => '',
      'suffix' => '',
      'size' => 60,
      'weight' => 0,
      'validate' => array(),
      'preprocess' => array(),
      'postprocess' => array(),
    );

#### Text fields

    $options += array(
      'type' => 'textfield',
    );

#### Autocomplete

    $options += array(
      'type' => 'autocomplete',
      'autocomplete_path' => '',
      'options' => array(),
      'min_length' => 3,
    );

if options array is defined, it is used as source for the autocomplete widget,
otherwise autocomplete_path is used

#### Spinners

    $options += array(
      'type' => 'spinner',
      'min'  => NULL,
      'max'  => NULL,
      'step' => 1,
    );

#### Masked fields

    $options += array(
      'type' => 'maskedfield',
      'mask' => '',
    );

#### Password fields

    $options += array(
      'type' => 'password',
      'with_confirm' => FALSE,
      'confirm_string' => 'Confirm password',
    );

#### Text areas

    $options += array(
      'type' => 'textarea',
      'rows' => 5,
      'resizable' => FALSE,
    );

#### Submit buttons

    $options += array(
      'type' => 'submit',
      'js_button' => FALSE,
    );

They are always valid.

#### Reset buttons

    $options += array(
      'type' => 'reset',
    );

They are always valid.

#### Buttons

    $options += array(
      'type' => 'button',
      'label' => '',
      'js_button' => FALSE,
    );

#### Image Buttons

    $options += array(
      'type' => 'image_button',
      'src' => '',
      'alt' => '',
      'js_button' => FALSE,
    );

They are always valid.
The value after submit is an array containing fields x,y

#### Select lists

    $options += array(
      'type' => 'select',
      'options' => array(),
      'multiple' => FALSE,
    );

#### Select Menus

    $options += array(
      'type' => 'selectmenu',
    );

#### Sliders

    $options += array(
      'type' => 'slider',
      'options' => array(),
    );

#### Radio buttons

    $options += array(
      'type' => 'radios',
      'options' => array(),
    );

#### Checkboxes

    $options += array(
      'type' => 'checkboxes',
      'options' => array(),
    );

#### Checkbox

    $options += array(
      'type' => 'checkbox',
    );

#### Hidden values

    $options += array(
      'type' => 'hidden',
    );

#### Markup

    $options += array(
      'type' => 'markup',
    );

Markup values are not passed to the values() function

#### Values

    $options += array(
      'type' => 'value',
    );

Values are passed with the form on submit but are not shown during render.
They are always valid.

#### Files

    $options += array(
      'type' => 'file',
      'destination' => '',
    );

#### Dates

    $options += array(
      'type' => 'date',
      'start_year' => '',
      'end_year' => '',
      'granularity' => 'day', // one of: year, month or day
      'js_selects' => FALSE,
    );
    $options['default_value'] = array(
      'year'=>'',
      'month'=>'',
      'day'=>'',
    );

#### Date Pickers

    $options += array(
      'type' => 'datepicker',
      'date_format' => 'yy-mm-dd',
      'change_month' => FALSE,
      'change_year' => FALSE,
      'mindate' => '-10Y',
      'maxdate' => '+10Y',
      'yearrange' => '-10:+10',
      'disabled_dates' => array(),
    );

#### Times

    $options += array(
      'type' => 'time',
      'granularity' => 'seconds', // one of: hours, minutes or seconds
      'js_selects' => FALSE,
    );
    $options['default_value'] = array(
      'hours'=>'',
      'minutes'=>'',
      'seconds'=>'',
    );

#### DateTimes

    $options += array(
      'type' => 'datetime',
      'start_year' => '',
      'end_year' => '',
      'granularity' => 'day - seconds', // one of: year, month, day, hours, minutes or seconds
      'js_selects' => FALSE,
    );
    $options['default_value'] = array(
      'year'=>'',
      'month'=>'',
      'day'=>'',
      'hours'=>'',
      'minutes'=>'',
      'seconds'=>'',
    );

#### Recapthas

    $options += array(
      'type' => 'recapcha',
      'publickey' => '',
      'privatekey' => '',
    );

  be sure you have loaded recaptchalib.php
  ( https://code.google.com/p/recaptcha/downloads/list?q=label:phplib-Latest )

#### Tag containers

    $options += array(
      'type' => 'tag_container',
      'tag' => 'div',
    );

#### Field sets

    $options += array(
      'type' => 'fieldset',
    );

#### Tabs

    $options += array(
      'type' => 'tabs',
    );

#### Accordions

    $options += array(
      'type' => 'accordion',
    );

#### Sortables

    $options += array(
      'type' => 'sortable',
      'handle_position' => 'left',
    );

    $options += array(
      'type' => 'sortable_table',
      'handle_position' => 'left',
      'table_header' => array(),
    );

### Validators reference

Required, Max Length, Min Length, Exact Length, Regular Expression, Alpha,
Alpha-numeric, Alpha-numeric with dashes, Numeric, Integer, Field matching,
Email, File extension, File not exists, File max size, File type.



### Processors reference

For security reasons, all user submitted data should be filtered before use.
This Forms API library helps you by providing some useful filtering tools that
will process submitted input. You can use these in your submit functions as and
when you need them, or you can have them run automatically during forms
processing. You can specify processors to run on a field before or after the
field is passed for validation.

Plain, Trim, LTrim, RTrim, XSS, XSS Weak, Addslashes
