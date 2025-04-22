<?php
/* @noinspection ALL */
// @formatter:off
// phpcs:ignoreFile

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 12.2.0.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */
namespace App\Facades {
    /**
     * 
     *
     */
    class TPaySignatureValidatorFacade {
        /**
         * 
         *
         * @static 
         */
        public static function confirm($webHookBody, $jws)
        {
            /** @var \App\Services\TPaySignatureValidator $instance */
            return $instance->confirm($webHookBody, $jws);
        }

        /**
         * 
         *
         * @static 
         */
        public static function getCertificate($x5u)
        {
            /** @var \App\Services\TPaySignatureValidator $instance */
            return $instance->getCertificate($x5u);
        }

        /**
         * 
         *
         * @static 
         */
        public static function getTrustedCertificate()
        {
            /** @var \App\Services\TPaySignatureValidator $instance */
            return $instance->getTrustedCertificate();
        }

            }
    }

namespace L5Swagger {
    /**
     * 
     *
     */
    class L5SwaggerFacade {
        /**
         * Generate necessary documentation files by scanning and processing the required data.
         *
         * @return void 
         * @throws L5SwaggerException
         * @throws Exception
         * @static 
         */
        public static function generateDocs()
        {
            /** @var \L5Swagger\Generator $instance */
            $instance->generateDocs();
        }

            }
    }

namespace Illuminate\Http {
    /**
     * 
     *
     */
    class Request {
        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param array $rules
         * @param mixed $params
         * @static 
         */
        public static function validate($rules, ...$params)
        {
            return \Illuminate\Http\Request::validate($rules, ...$params);
        }

        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param string $errorBag
         * @param array $rules
         * @param mixed $params
         * @static 
         */
        public static function validateWithBag($errorBag, $rules, ...$params)
        {
            return \Illuminate\Http\Request::validateWithBag($errorBag, $rules, ...$params);
        }

        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $absolute
         * @static 
         */
        public static function hasValidSignature($absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignature($absolute);
        }

        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @static 
         */
        public static function hasValidRelativeSignature()
        {
            return \Illuminate\Http\Request::hasValidRelativeSignature();
        }

        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @param mixed $absolute
         * @static 
         */
        public static function hasValidSignatureWhileIgnoring($ignoreQuery = [], $absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignatureWhileIgnoring($ignoreQuery, $absolute);
        }

        /**
         * 
         *
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @static 
         */
        public static function hasValidRelativeSignatureWhileIgnoring($ignoreQuery = [])
        {
            return \Illuminate\Http\Request::hasValidRelativeSignatureWhileIgnoring($ignoreQuery);
        }

            }
    }


namespace  {
    class TPaySignatureValidator extends \App\Facades\TPaySignatureValidatorFacade {}
    class L5Swagger extends \L5Swagger\L5SwaggerFacade {}
}





