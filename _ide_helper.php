<?php
/* @noinspection ALL */
// @formatter:off
// phpcs:ignoreFile

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 12.14.1.
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

namespace AnourValar\EloquentSerialize\Facades {
    /**
     * 
     *
     */
    class EloquentSerializeFacade {
        /**
         * Pack
         *
         * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
         * @return string 
         * @throws \RuntimeException
         * @static 
         */
        public static function serialize($builder)
        {
            /** @var \AnourValar\EloquentSerialize\Service $instance */
            return $instance->serialize($builder);
        }

        /**
         * Unpack
         *
         * @param mixed $package
         * @throws \LogicException
         * @return \Illuminate\Database\Eloquent\Builder 
         * @static 
         */
        public static function unserialize($package)
        {
            /** @var \AnourValar\EloquentSerialize\Service $instance */
            return $instance->unserialize($package);
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

namespace Livewire {
    /**
     * 
     *
     * @see \Livewire\LivewireManager
     */
    class Livewire {
        /**
         * 
         *
         * @static 
         */
        public static function setProvider($provider)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setProvider($provider);
        }

        /**
         * 
         *
         * @static 
         */
        public static function provide($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->provide($callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function component($name, $class = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->component($name, $class);
        }

        /**
         * 
         *
         * @static 
         */
        public static function componentHook($hook)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->componentHook($hook);
        }

        /**
         * 
         *
         * @static 
         */
        public static function propertySynthesizer($synth)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->propertySynthesizer($synth);
        }

        /**
         * 
         *
         * @static 
         */
        public static function directive($name, $callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->directive($name, $callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function precompiler($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->precompiler($callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function new($name, $id = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->new($name, $id);
        }

        /**
         * 
         *
         * @static 
         */
        public static function isDiscoverable($componentNameOrClass)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isDiscoverable($componentNameOrClass);
        }

        /**
         * 
         *
         * @static 
         */
        public static function resolveMissingComponent($resolver)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->resolveMissingComponent($resolver);
        }

        /**
         * 
         *
         * @static 
         */
        public static function mount($name, $params = [], $key = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->mount($name, $params, $key);
        }

        /**
         * 
         *
         * @static 
         */
        public static function snapshot($component)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->snapshot($component);
        }

        /**
         * 
         *
         * @static 
         */
        public static function fromSnapshot($snapshot)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->fromSnapshot($snapshot);
        }

        /**
         * 
         *
         * @static 
         */
        public static function listen($eventName, $callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->listen($eventName, $callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function current()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->current();
        }

        /**
         * 
         *
         * @static 
         */
        public static function findSynth($keyOrTarget, $component)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->findSynth($keyOrTarget, $component);
        }

        /**
         * 
         *
         * @static 
         */
        public static function update($snapshot, $diff, $calls)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->update($snapshot, $diff, $calls);
        }

        /**
         * 
         *
         * @static 
         */
        public static function updateProperty($component, $path, $value)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->updateProperty($component, $path, $value);
        }

        /**
         * 
         *
         * @static 
         */
        public static function isLivewireRequest()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isLivewireRequest();
        }

        /**
         * 
         *
         * @static 
         */
        public static function componentHasBeenRendered()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->componentHasBeenRendered();
        }

        /**
         * 
         *
         * @static 
         */
        public static function forceAssetInjection()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->forceAssetInjection();
        }

        /**
         * 
         *
         * @static 
         */
        public static function setUpdateRoute($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setUpdateRoute($callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function getUpdateUri()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->getUpdateUri();
        }

        /**
         * 
         *
         * @static 
         */
        public static function setScriptRoute($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setScriptRoute($callback);
        }

        /**
         * 
         *
         * @static 
         */
        public static function useScriptTagAttributes($attributes)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->useScriptTagAttributes($attributes);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withUrlParams($params)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withUrlParams($params);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withQueryParams($params)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withQueryParams($params);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withCookie($name, $value)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withCookie($name, $value);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withCookies($cookies)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withCookies($cookies);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withHeaders($headers)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withHeaders($headers);
        }

        /**
         * 
         *
         * @static 
         */
        public static function withoutLazyLoading()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withoutLazyLoading();
        }

        /**
         * 
         *
         * @static 
         */
        public static function test($name, $params = [])
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->test($name, $params);
        }

        /**
         * 
         *
         * @static 
         */
        public static function visit($name)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->visit($name);
        }

        /**
         * 
         *
         * @static 
         */
        public static function actingAs($user, $driver = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->actingAs($user, $driver);
        }

        /**
         * 
         *
         * @static 
         */
        public static function isRunningServerless()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isRunningServerless();
        }

        /**
         * 
         *
         * @static 
         */
        public static function addPersistentMiddleware($middleware)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->addPersistentMiddleware($middleware);
        }

        /**
         * 
         *
         * @static 
         */
        public static function setPersistentMiddleware($middleware)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setPersistentMiddleware($middleware);
        }

        /**
         * 
         *
         * @static 
         */
        public static function getPersistentMiddleware()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->getPersistentMiddleware();
        }

        /**
         * 
         *
         * @static 
         */
        public static function flushState()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->flushState();
        }

        /**
         * 
         *
         * @static 
         */
        public static function originalUrl()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalUrl();
        }

        /**
         * 
         *
         * @static 
         */
        public static function originalPath()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalPath();
        }

        /**
         * 
         *
         * @static 
         */
        public static function originalMethod()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalMethod();
        }

            }
    }

namespace Illuminate\Support {
    /**
     * 
     *
     */
    class Str {
        /**
         * 
         *
         * @see \Filament\Support\SupportServiceProvider::packageBooted()
         * @param string $html
         * @return string 
         * @static 
         */
        public static function sanitizeHtml($html)
        {
            return \Illuminate\Support\Str::sanitizeHtml($html);
        }

        /**
         * 
         *
         * @see \Filament\Support\SupportServiceProvider::packageBooted()
         * @param string $value
         * @return string 
         * @static 
         */
        public static function ucwords($value)
        {
            return \Illuminate\Support\Str::ucwords($value);
        }

            }
    /**
     * 
     *
     */
    class Stringable {
        /**
         * 
         *
         * @see \Filament\Support\SupportServiceProvider::packageBooted()
         * @return \Illuminate\Support\Stringable 
         * @static 
         */
        public static function sanitizeHtml()
        {
            return \Illuminate\Support\Stringable::sanitizeHtml();
        }

        /**
         * 
         *
         * @see \Filament\Support\SupportServiceProvider::packageBooted()
         * @return \Illuminate\Support\Stringable 
         * @static 
         */
        public static function ucwords()
        {
            return \Illuminate\Support\Stringable::ucwords();
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

namespace Illuminate\Database\Query {
    /**
     * 
     *
     */
    class Builder {
        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\QueryBuilderExtraMethods::getGroupBy()
         * @static 
         */
        public static function getGroupBy()
        {
            return \Illuminate\Database\Query\Builder::getGroupBy();
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\QueryBuilderExtraMethods::getSelect()
         * @static 
         */
        public static function getSelect()
        {
            return \Illuminate\Database\Query\Builder::getSelect();
        }

            }
    }

namespace Illuminate\Database\Eloquent\Relations {
    /**
     * 
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     * @template TResult
     * @mixin \Illuminate\Database\Eloquent\Builder<TRelatedModel>
     */
    class Relation {
        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoins()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @param string|null $morphable
         * @param bool $hasCheck
         * @static 
         */
        public static function performJoinForEloquentPowerJoins($builder, $joinType = 'leftJoin', $callback = null, $alias = null, $disableExtraConditions = false, $morphable = null, $hasCheck = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoins($builder, $joinType, $callback, $alias, $disableExtraConditions, $morphable, $hasCheck);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForBelongsTo()
         * @param mixed $query
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForBelongsTo($query, $joinType, $callback = null, $alias = null, $disableExtraConditions = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForBelongsTo($query, $joinType, $callback, $alias, $disableExtraConditions);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForBelongsToMany()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForBelongsToMany($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForBelongsToMany($builder, $joinType, $callback, $alias, $disableExtraConditions);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForMorphToMany()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForMorphToMany($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForMorphToMany($builder, $joinType, $callback, $alias, $disableExtraConditions);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForMorph()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForMorph($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForMorph($builder, $joinType, $callback, $alias, $disableExtraConditions);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForMorphTo()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @param string|null $morphable
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForMorphTo($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false, $morphable = null)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForMorphTo($builder, $joinType, $callback, $alias, $disableExtraConditions, $morphable);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForHasMany()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @param bool $hasCheck
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForHasMany($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false, $hasCheck = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForHasMany($builder, $joinType, $callback, $alias, $disableExtraConditions, $hasCheck);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performJoinForEloquentPowerJoinsForHasManyThrough()
         * @param mixed $builder
         * @param mixed $joinType
         * @param mixed $callback
         * @param mixed $alias
         * @param bool $disableExtraConditions
         * @static 
         */
        public static function performJoinForEloquentPowerJoinsForHasManyThrough($builder, $joinType, $callback = null, $alias = null, $disableExtraConditions = false)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performJoinForEloquentPowerJoinsForHasManyThrough($builder, $joinType, $callback, $alias, $disableExtraConditions);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::performHavingForEloquentPowerJoins()
         * @param mixed $builder
         * @param mixed $operator
         * @param mixed $count
         * @param string|null $morphable
         * @static 
         */
        public static function performHavingForEloquentPowerJoins($builder, $operator, $count, $morphable = null)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::performHavingForEloquentPowerJoins($builder, $operator, $count, $morphable);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::usesSoftDeletes()
         * @param mixed $model
         * @static 
         */
        public static function usesSoftDeletes($model)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::usesSoftDeletes($model);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::getThroughParent()
         * @static 
         */
        public static function getThroughParent()
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::getThroughParent();
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::getFarParent()
         * @static 
         */
        public static function getFarParent()
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::getFarParent();
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::applyExtraConditions()
         * @param \Kirschbaum\PowerJoins\PowerJoinClause $join
         * @static 
         */
        public static function applyExtraConditions($join)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::applyExtraConditions($join);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::applyBasicCondition()
         * @param mixed $join
         * @param mixed $condition
         * @static 
         */
        public static function applyBasicCondition($join, $condition)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::applyBasicCondition($join, $condition);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::applyNullCondition()
         * @param mixed $join
         * @param mixed $condition
         * @static 
         */
        public static function applyNullCondition($join, $condition)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::applyNullCondition($join, $condition);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::applyNotNullCondition()
         * @param mixed $join
         * @param mixed $condition
         * @static 
         */
        public static function applyNotNullCondition($join, $condition)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::applyNotNullCondition($join, $condition);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::applyNestedCondition()
         * @param mixed $join
         * @param mixed $condition
         * @static 
         */
        public static function applyNestedCondition($join, $condition)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::applyNestedCondition($join, $condition);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::shouldNotApplyExtraCondition()
         * @param mixed $condition
         * @static 
         */
        public static function shouldNotApplyExtraCondition($condition)
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::shouldNotApplyExtraCondition($condition);
        }

        /**
         * 
         *
         * @see \Kirschbaum\PowerJoins\Mixins\RelationshipsExtraMethods::getPowerJoinExistenceCompareKey()
         * @static 
         */
        public static function getPowerJoinExistenceCompareKey()
        {
            return \Illuminate\Database\Eloquent\Relations\Relation::getPowerJoinExistenceCompareKey();
        }

            }
    }

namespace Illuminate\Routing {
    /**
     * 
     *
     */
    class Route {
        /**
         * 
         *
         * @see \Livewire\Features\SupportLazyLoading\SupportLazyLoading::registerRouteMacro()
         * @param mixed $enabled
         * @static 
         */
        public static function lazy($enabled = true)
        {
            return \Illuminate\Routing\Route::lazy($enabled);
        }

        /**
         * 
         *
         * @see \Spatie\Permission\PermissionServiceProvider::registerMacroHelpers()
         * @param mixed $roles
         * @static 
         */
        public static function role($roles = [])
        {
            return \Illuminate\Routing\Route::role($roles);
        }

        /**
         * 
         *
         * @see \Spatie\Permission\PermissionServiceProvider::registerMacroHelpers()
         * @param mixed $permissions
         * @static 
         */
        public static function permission($permissions = [])
        {
            return \Illuminate\Routing\Route::permission($permissions);
        }

            }
    }

namespace Livewire\Features\SupportTesting {
    /**
     * 
     *
     * @mixin \Illuminate\Testing\TestResponse
     */
    class Testable {
        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::mountAction()
         * @param array|string $name
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function mountAction($name, $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::mountAction($name, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::unmountAction()
         * @return static 
         * @static 
         */
        public static function unmountAction()
        {
            return \Livewire\Features\SupportTesting\Testable::unmountAction();
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::setActionData()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function setActionData($data)
        {
            return \Livewire\Features\SupportTesting\Testable::setActionData($data);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDataSet()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function assertActionDataSet($data)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDataSet($data);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::callAction()
         * @param array|string $name
         * @param array $data
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callAction($name, $data = [], $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callAction($name, $data, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::callMountedAction()
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callMountedAction($arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callMountedAction($arguments);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionExists()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionExists($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionExists($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDoesNotExist()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionDoesNotExist($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDoesNotExist($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionVisible()
         * @param array|string $name
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function assertActionVisible($name, $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionVisible($name, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionHidden()
         * @param array|string $name
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function assertActionHidden($name, $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHidden($name, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionEnabled()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionEnabled($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionEnabled($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDisabled()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionDisabled($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDisabled($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionHasIcon()
         * @param array|string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionHasIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHasIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDoesNotHaveIcon()
         * @param array|string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionDoesNotHaveIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDoesNotHaveIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionHasLabel()
         * @param array|string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionHasLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHasLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDoesNotHaveLabel()
         * @param array|string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionDoesNotHaveLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDoesNotHaveLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionHasColor()
         * @param array|string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionHasColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHasColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDoesNotHaveColor()
         * @param array|string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionDoesNotHaveColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDoesNotHaveColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionHasUrl()
         * @param array|string $name
         * @param string $url
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionHasUrl($name, $url, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHasUrl($name, $url, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionDoesNotHaveUrl()
         * @param array|string $name
         * @param string $url
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionDoesNotHaveUrl($name, $url, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionDoesNotHaveUrl($name, $url, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionShouldOpenUrlInNewTab()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionShouldOpenUrlInNewTab($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionShouldOpenUrlInNewTab($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionShouldNotOpenUrlInNewTab()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertActionShouldNotOpenUrlInNewTab($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionShouldNotOpenUrlInNewTab($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionNotMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionNotMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionNotMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionHalted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHalted($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertActionHeld($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionHeld($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertHasActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertHasNoActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasNoActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::assertActionListInOrder()
         * @param array $names
         * @param array $actions
         * @param string $actionType
         * @param string $actionClass
         * @return self 
         * @static 
         */
        public static function assertActionListInOrder($names, $actions, $actionType, $actionClass)
        {
            return \Livewire\Features\SupportTesting\Testable::assertActionListInOrder($names, $actions, $actionType, $actionClass);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::parseActionName()
         * @param string $name
         * @return string 
         * @static 
         */
        public static function parseActionName($name)
        {
            return \Livewire\Features\SupportTesting\Testable::parseActionName($name);
        }

        /**
         * 
         *
         * @see \Filament\Actions\Testing\TestsActions::parseNestedActionName()
         * @param array|string $name
         * @return array 
         * @static 
         */
        public static function parseNestedActionName($name)
        {
            return \Livewire\Features\SupportTesting\Testable::parseNestedActionName($name);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::fillForm()
         * @param \Closure|array $state
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function fillForm($state = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::fillForm($state, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormSet()
         * @param \Closure|array $state
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormSet($state, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormSet($state, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertHasFormErrors()
         * @param array $keys
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertHasFormErrors($keys = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasFormErrors($keys, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertHasNoFormErrors()
         * @param array $keys
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertHasNoFormErrors($keys = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoFormErrors($keys, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormExists()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertFormExists($name = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormExists($name);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormComponentExists()
         * @param string $componentKey
         * @param \Closure|string $formName
         * @param \Closure|null $checkComponentUsing
         * @return static 
         * @static 
         */
        public static function assertFormComponentExists($componentKey, $formName = 'form', $checkComponentUsing = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentExists($componentKey, $formName, $checkComponentUsing);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormComponentDoesNotExist()
         * @param string $componentKey
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentDoesNotExist($componentKey, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentDoesNotExist($componentKey, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldExists()
         * @param string $fieldName
         * @param \Closure|string $formName
         * @param \Closure|null $checkFieldUsing
         * @return static 
         * @static 
         */
        public static function assertFormFieldExists($fieldName, $formName = 'form', $checkFieldUsing = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldExists($fieldName, $formName, $checkFieldUsing);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldDoesNotExist()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldDoesNotExist($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldDoesNotExist($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldIsDisabled()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldIsDisabled($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldIsDisabled($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldIsEnabled()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldIsEnabled($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldIsEnabled($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldIsReadOnly()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldIsReadOnly($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldIsReadOnly($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldIsHidden()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldIsHidden($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldIsHidden($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertFormFieldIsVisible()
         * @param string $fieldName
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormFieldIsVisible($fieldName, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormFieldIsVisible($fieldName, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertWizardStepExists()
         * @param int $step
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertWizardStepExists($step, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertWizardStepExists($step, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::assertWizardCurrentStep()
         * @param int $step
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertWizardCurrentStep($step, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertWizardCurrentStep($step, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::goToWizardStep()
         * @param int $step
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function goToWizardStep($step, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::goToWizardStep($step, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::goToNextWizardStep()
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function goToNextWizardStep($formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::goToNextWizardStep($formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsForms::goToPreviousWizardStep()
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function goToPreviousWizardStep($formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::goToPreviousWizardStep($formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::mountFormComponentAction()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function mountFormComponentAction($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::mountFormComponentAction($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::unmountFormComponentAction()
         * @return static 
         * @static 
         */
        public static function unmountFormComponentAction()
        {
            return \Livewire\Features\SupportTesting\Testable::unmountFormComponentAction();
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::setFormComponentActionData()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function setFormComponentActionData($data)
        {
            return \Livewire\Features\SupportTesting\Testable::setFormComponentActionData($data);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDataSet()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDataSet($data)
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDataSet($data);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::callFormComponentAction()
         * @param array|string $component
         * @param array|string $name
         * @param array $data
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function callFormComponentAction($component, $name, $data = [], $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::callFormComponentAction($component, $name, $data, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::callMountedFormComponentAction()
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callMountedFormComponentAction($arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callMountedFormComponentAction($arguments);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionExists()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionExists($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionExists($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDoesNotExist()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDoesNotExist($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDoesNotExist($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionVisible()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionVisible($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionVisible($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionHidden()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHidden($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHidden($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionEnabled()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionEnabled($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionEnabled($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDisabled()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDisabled($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDisabled($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionHasIcon()
         * @param array|string $component
         * @param array|string $name
         * @param string $icon
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHasIcon($component, $name, $icon, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHasIcon($component, $name, $icon, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDoesNotHaveIcon()
         * @param array|string $component
         * @param array|string $name
         * @param string $icon
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDoesNotHaveIcon($component, $name, $icon, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDoesNotHaveIcon($component, $name, $icon, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionHasLabel()
         * @param array|string $component
         * @param array|string $name
         * @param string $label
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHasLabel($component, $name, $label, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHasLabel($component, $name, $label, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDoesNotHaveLabel()
         * @param array|string $component
         * @param array|string $name
         * @param string $label
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDoesNotHaveLabel($component, $name, $label, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDoesNotHaveLabel($component, $name, $label, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionHasColor()
         * @param array|string $component
         * @param array|string $name
         * @param array|string $color
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHasColor($component, $name, $color, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHasColor($component, $name, $color, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDoesNotHaveColor()
         * @param array|string $component
         * @param array|string $name
         * @param array|string $color
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDoesNotHaveColor($component, $name, $color, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDoesNotHaveColor($component, $name, $color, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionHasUrl()
         * @param array|string $component
         * @param array|string $name
         * @param string $url
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHasUrl($component, $name, $url, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHasUrl($component, $name, $url, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionDoesNotHaveUrl()
         * @param array|string $component
         * @param array|string $name
         * @param string $url
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionDoesNotHaveUrl($component, $name, $url, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionDoesNotHaveUrl($component, $name, $url, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionShouldOpenUrlInNewTab()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionShouldOpenUrlInNewTab($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionShouldOpenUrlInNewTab($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionShouldNotOpenUrlInNewTab()
         * @param array|string $component
         * @param array|string $name
         * @param array $arguments
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionShouldNotOpenUrlInNewTab($component, $name, $arguments = [], $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionShouldNotOpenUrlInNewTab($component, $name, $arguments, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionMounted()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionMounted($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionMounted($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionNotMounted()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionNotMounted($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionNotMounted($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertFormComponentActionMounted()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return static 
         * @static 
         */
        public static function assertFormComponentActionHalted($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::assertFormComponentActionHalted($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertHasFormComponentActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasFormComponentActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasFormComponentActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::assertHasNoFormComponentActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasNoFormComponentActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoFormComponentActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::getNestedFormComponentActionComponentAndName()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @param array $arguments
         * @return array 
         * @static 
         */
        public static function getNestedFormComponentActionComponentAndName($component, $name, $formName = 'form', $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::getNestedFormComponentActionComponentAndName($component, $name, $formName, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Forms\Testing\TestsComponentActions::parseNestedFormComponentActionComponentAndName()
         * @param array|string $component
         * @param array|string $name
         * @param string $formName
         * @return array 
         * @static 
         */
        public static function parseNestedFormComponentActionComponentAndName($component, $name, $formName = 'form')
        {
            return \Livewire\Features\SupportTesting\Testable::parseNestedFormComponentActionComponentAndName($component, $name, $formName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::mountInfolistAction()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function mountInfolistAction($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::mountInfolistAction($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::unmountInfolistAction()
         * @return static 
         * @static 
         */
        public static function unmountInfolistAction()
        {
            return \Livewire\Features\SupportTesting\Testable::unmountInfolistAction();
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::setInfolistActionData()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function setInfolistActionData($data)
        {
            return \Livewire\Features\SupportTesting\Testable::setInfolistActionData($data);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDataSet()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDataSet($data)
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDataSet($data);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::callInfolistAction()
         * @param string $component
         * @param array|string $name
         * @param array $data
         * @param array $arguments
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function callInfolistAction($component, $name, $data = [], $arguments = [], $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::callInfolistAction($component, $name, $data, $arguments, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::callMountedInfolistAction()
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callMountedInfolistAction($arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callMountedInfolistAction($arguments);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionExists()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionExists($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionExists($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDoesNotExist()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDoesNotExist($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDoesNotExist($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionVisible()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionVisible($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionVisible($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionHidden()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHidden($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHidden($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionEnabled()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionEnabled($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionEnabled($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDisabled()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDisabled($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDisabled($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionHasIcon()
         * @param string $component
         * @param array|string $name
         * @param string $icon
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHasIcon($component, $name, $icon, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHasIcon($component, $name, $icon, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDoesNotHaveIcon()
         * @param string $component
         * @param array|string $name
         * @param string $icon
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDoesNotHaveIcon($component, $name, $icon, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDoesNotHaveIcon($component, $name, $icon, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionHasLabel()
         * @param string $component
         * @param array|string $name
         * @param string $label
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHasLabel($component, $name, $label, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHasLabel($component, $name, $label, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDoesNotHaveLabel()
         * @param string $component
         * @param array|string $name
         * @param string $label
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDoesNotHaveLabel($component, $name, $label, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDoesNotHaveLabel($component, $name, $label, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionHasColor()
         * @param string $component
         * @param array|string $name
         * @param array|string $color
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHasColor($component, $name, $color, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHasColor($component, $name, $color, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDoesNotHaveColor()
         * @param string $component
         * @param array|string $name
         * @param array|string $color
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDoesNotHaveColor($component, $name, $color, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDoesNotHaveColor($component, $name, $color, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionHasUrl()
         * @param string $component
         * @param array|string $name
         * @param string $url
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHasUrl($component, $name, $url, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHasUrl($component, $name, $url, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionDoesNotHaveUrl()
         * @param string $component
         * @param array|string $name
         * @param string $url
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionDoesNotHaveUrl($component, $name, $url, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionDoesNotHaveUrl($component, $name, $url, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionShouldOpenUrlInNewTab()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionShouldOpenUrlInNewTab($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionShouldOpenUrlInNewTab($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionShouldNotOpenUrlInNewTab()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionShouldNotOpenUrlInNewTab($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionShouldNotOpenUrlInNewTab($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionMounted()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionMounted($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionMounted($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionNotMounted()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionNotMounted($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionNotMounted($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertInfolistActionMounted()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return static 
         * @static 
         */
        public static function assertInfolistActionHalted($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::assertInfolistActionHalted($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertHasInfolistActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasInfolistActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasInfolistActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::assertHasNoInfolistActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasNoInfolistActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoInfolistActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Infolists\Testing\TestsActions::getNestedInfolistActionComponentAndName()
         * @param string $component
         * @param array|string $name
         * @param string $infolistName
         * @return array 
         * @static 
         */
        public static function getNestedInfolistActionComponentAndName($component, $name, $infolistName = 'infolist')
        {
            return \Livewire\Features\SupportTesting\Testable::getNestedInfolistActionComponentAndName($component, $name, $infolistName);
        }

        /**
         * 
         *
         * @see \Filament\Notifications\Testing\TestsNotifications::assertNotified()
         * @param \Filament\Notifications\Notification|string|null $notification
         * @return static 
         * @static 
         */
        public static function assertNotified($notification = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertNotified($notification);
        }

        /**
         * 
         *
         * @see \Filament\Notifications\Testing\TestsNotifications::assertNotNotified()
         * @param \Filament\Notifications\Notification|string|null $notification
         * @return static 
         * @static 
         */
        public static function assertNotNotified($notification = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertNotNotified($notification);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::mountTableAction()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function mountTableAction($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::mountTableAction($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::unmountTableAction()
         * @return static 
         * @static 
         */
        public static function unmountTableAction()
        {
            return \Livewire\Features\SupportTesting\Testable::unmountTableAction();
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::setTableActionData()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function setTableActionData($data)
        {
            return \Livewire\Features\SupportTesting\Testable::setTableActionData($data);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDataSet()
         * @param \Closure|array $state
         * @return static 
         * @static 
         */
        public static function assertTableActionDataSet($state)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDataSet($state);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::callTableAction()
         * @param array|string $name
         * @param mixed $record
         * @param array $data
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callTableAction($name, $record = null, $data = [], $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callTableAction($name, $record, $data, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::callMountedTableAction()
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callMountedTableAction($arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callMountedTableAction($arguments);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionExists()
         * @param array|string $name
         * @param \Closure|null $checkActionUsing
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionExists($name, $checkActionUsing = null, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionExists($name, $checkActionUsing, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDoesNotExist()
         * @param array|string $name
         * @param \Closure|null $checkActionUsing
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDoesNotExist($name, $checkActionUsing = null, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDoesNotExist($name, $checkActionUsing, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionsExistInOrder()
         * @param array $names
         * @return static 
         * @static 
         */
        public static function assertTableActionsExistInOrder($names)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionsExistInOrder($names);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableHeaderActionsExistInOrder()
         * @param array $names
         * @return static 
         * @static 
         */
        public static function assertTableHeaderActionsExistInOrder($names)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableHeaderActionsExistInOrder($names);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableEmptyStateActionsExistInOrder()
         * @param array $names
         * @return static 
         * @static 
         */
        public static function assertTableEmptyStateActionsExistInOrder($names)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableEmptyStateActionsExistInOrder($names);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionVisible()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionVisible($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionVisible($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionHidden()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionHidden($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHidden($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionEnabled()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionEnabled($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionEnabled($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDisabled()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDisabled($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDisabled($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionHasIcon()
         * @param array|string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionHasIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHasIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDoesNotHaveIcon()
         * @param array|string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDoesNotHaveIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDoesNotHaveIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionHasLabel()
         * @param array|string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionHasLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHasLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDoesNotHaveLabel()
         * @param array|string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDoesNotHaveLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDoesNotHaveLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionHasColor()
         * @param array|string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionHasColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHasColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDoesNotHaveColor()
         * @param array|string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDoesNotHaveColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDoesNotHaveColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionHasUrl()
         * @param array|string $name
         * @param string $url
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionHasUrl($name, $url, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHasUrl($name, $url, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionDoesNotHaveUrl()
         * @param array|string $name
         * @param string $url
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionDoesNotHaveUrl($name, $url, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionDoesNotHaveUrl($name, $url, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionShouldOpenUrlInNewTab()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionShouldOpenUrlInNewTab($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionShouldOpenUrlInNewTab($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionShouldNotOpenUrlInNewTab()
         * @param array|string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableActionShouldNotOpenUrlInNewTab($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionShouldNotOpenUrlInNewTab($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertTableActionMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionNotMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertTableActionNotMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionNotMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertTableActionHalted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHalted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertTableActionMounted()
         * @param array|string $name
         * @return static 
         * @static 
         */
        public static function assertTableActionHeld($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableActionHeld($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertHasTableActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasTableActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasTableActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsActions::assertHasNoTableActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasNoTableActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoTableActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::mountTableBulkAction()
         * @param string $name
         * @param \Illuminate\Support\Collection|array $records
         * @return static 
         * @static 
         */
        public static function mountTableBulkAction($name, $records)
        {
            return \Livewire\Features\SupportTesting\Testable::mountTableBulkAction($name, $records);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::setTableBulkActionData()
         * @param array $data
         * @return static 
         * @static 
         */
        public static function setTableBulkActionData($data)
        {
            return \Livewire\Features\SupportTesting\Testable::setTableBulkActionData($data);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDataSet()
         * @param \Closure|array $state
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDataSet($state)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDataSet($state);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::callTableBulkAction()
         * @param string $name
         * @param \Illuminate\Support\Collection|array $records
         * @param array $data
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callTableBulkAction($name, $records, $data = [], $arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callTableBulkAction($name, $records, $data, $arguments);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::callMountedTableBulkAction()
         * @param array $arguments
         * @return static 
         * @static 
         */
        public static function callMountedTableBulkAction($arguments = [])
        {
            return \Livewire\Features\SupportTesting\Testable::callMountedTableBulkAction($arguments);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionExists()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionExists($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionExists($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDoesNotExist()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDoesNotExist($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDoesNotExist($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionsExistInOrder()
         * @param array $names
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionsExistInOrder($names)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionsExistInOrder($names);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionVisible()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionVisible($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionVisible($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionHidden()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHidden($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHidden($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionEnabled()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionEnabled($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionEnabled($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDisabled()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDisabled($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDisabled($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionHasIcon()
         * @param string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHasIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHasIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDoesNotHaveIcon()
         * @param string $name
         * @param string $icon
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDoesNotHaveIcon($name, $icon, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDoesNotHaveIcon($name, $icon, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionHasLabel()
         * @param string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHasLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHasLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDoesNotHaveLabel()
         * @param string $name
         * @param string $label
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDoesNotHaveLabel($name, $label, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDoesNotHaveLabel($name, $label, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionHasColor()
         * @param string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHasColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHasColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionDoesNotHaveColor()
         * @param string $name
         * @param array|string $color
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionDoesNotHaveColor($name, $color, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionDoesNotHaveColor($name, $color, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionMounted()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionNotMounted()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionNotMounted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionNotMounted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionMounted()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHalted($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHalted($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertTableBulkActionMounted()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableBulkActionHeld($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableBulkActionHeld($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertHasTableBulkActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasTableBulkActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasTableBulkActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsBulkActions::assertHasNoTableBulkActionErrors()
         * @param array $keys
         * @return static 
         * @static 
         */
        public static function assertHasNoTableBulkActionErrors($keys = [])
        {
            return \Livewire\Features\SupportTesting\Testable::assertHasNoTableBulkActionErrors($keys);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertCanRenderTableColumn()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertCanRenderTableColumn($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertCanRenderTableColumn($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertCanNotRenderTableColumn()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertCanNotRenderTableColumn($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertCanNotRenderTableColumn($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnExists()
         * @param string $name
         * @param \Closure|null $checkColumnUsing
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnExists($name, $checkColumnUsing = null, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnExists($name, $checkColumnUsing, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnDoesNotExist()
         * @param string $name
         * @param \Closure|null $checkColumnUsing
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnDoesNotExist($name, $checkColumnUsing = null, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnDoesNotExist($name, $checkColumnUsing, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnVisible()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableColumnVisible($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnVisible($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnHidden()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableColumnHidden($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnHidden($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnStateSet()
         * @param string $name
         * @param mixed $value
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnStateSet($name, $value, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnStateSet($name, $value, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnStateNotSet()
         * @param string $name
         * @param mixed $value
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnStateNotSet($name, $value, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnStateNotSet($name, $value, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnFormattedStateSet()
         * @param string $name
         * @param mixed $value
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnFormattedStateSet($name, $value, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnFormattedStateSet($name, $value, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnFormattedStateNotSet()
         * @param string $name
         * @param mixed $value
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function assertTableColumnFormattedStateNotSet($name, $value, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnFormattedStateNotSet($name, $value, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnHasExtraAttributes()
         * @param string $name
         * @param array $attributes
         * @param mixed $record
         * @static 
         */
        public static function assertTableColumnHasExtraAttributes($name, $attributes, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnHasExtraAttributes($name, $attributes, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnDoesNotHaveExtraAttributes()
         * @param string $name
         * @param array $attributes
         * @param mixed $record
         * @static 
         */
        public static function assertTableColumnDoesNotHaveExtraAttributes($name, $attributes, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnDoesNotHaveExtraAttributes($name, $attributes, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnHasDescription()
         * @param string $name
         * @param mixed $description
         * @param mixed $record
         * @param string $position
         * @static 
         */
        public static function assertTableColumnHasDescription($name, $description, $record, $position = 'below')
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnHasDescription($name, $description, $record, $position);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableColumnDoesNotHaveDescription()
         * @param string $name
         * @param mixed $description
         * @param mixed $record
         * @param string $position
         * @static 
         */
        public static function assertTableColumnDoesNotHaveDescription($name, $description, $record, $position = 'below')
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnDoesNotHaveDescription($name, $description, $record, $position);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableSelectColumnHasOptions()
         * @param string $name
         * @param array $options
         * @param mixed $record
         * @static 
         */
        public static function assertTableSelectColumnHasOptions($name, $options, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableSelectColumnHasOptions($name, $options, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::assertTableSelectColumnDoesNotHaveOptions()
         * @param string $name
         * @param array $options
         * @param mixed $record
         * @static 
         */
        public static function assertTableSelectColumnDoesNotHaveOptions($name, $options, $record)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableSelectColumnDoesNotHaveOptions($name, $options, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::callTableColumnAction()
         * @param string $name
         * @param mixed $record
         * @return static 
         * @static 
         */
        public static function callTableColumnAction($name, $record = null)
        {
            return \Livewire\Features\SupportTesting\Testable::callTableColumnAction($name, $record);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::sortTable()
         * @param string|null $name
         * @param string|null $direction
         * @return static 
         * @static 
         */
        public static function sortTable($name = null, $direction = null)
        {
            return \Livewire\Features\SupportTesting\Testable::sortTable($name, $direction);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::searchTable()
         * @param string|null $search
         * @return static 
         * @static 
         */
        public static function searchTable($search = null)
        {
            return \Livewire\Features\SupportTesting\Testable::searchTable($search);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsColumns::searchTableColumns()
         * @param array $searches
         * @return static 
         * @static 
         */
        public static function searchTableColumns($searches)
        {
            return \Livewire\Features\SupportTesting\Testable::searchTableColumns($searches);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::filterTable()
         * @param string $name
         * @param mixed $data
         * @return static 
         * @static 
         */
        public static function filterTable($name, $data = null)
        {
            return \Livewire\Features\SupportTesting\Testable::filterTable($name, $data);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::resetTableFilters()
         * @return static 
         * @static 
         */
        public static function resetTableFilters()
        {
            return \Livewire\Features\SupportTesting\Testable::resetTableFilters();
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::removeTableFilter()
         * @param string $filter
         * @param string|null $field
         * @return static 
         * @static 
         */
        public static function removeTableFilter($filter, $field = null)
        {
            return \Livewire\Features\SupportTesting\Testable::removeTableFilter($filter, $field);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::removeTableFilters()
         * @return static 
         * @static 
         */
        public static function removeTableFilters()
        {
            return \Livewire\Features\SupportTesting\Testable::removeTableFilters();
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::assertTableFilterExists()
         * @param string $name
         * @param \Closure|null $checkFilterUsing
         * @return static 
         * @static 
         */
        public static function assertTableFilterExists($name, $checkFilterUsing = null)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableFilterExists($name, $checkFilterUsing);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::assertTableFilterVisible()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableFilterVisible($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableFilterVisible($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsFilters::assertTableFilterHidden()
         * @param string $name
         * @return static 
         * @static 
         */
        public static function assertTableFilterHidden($name)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableFilterHidden($name);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsRecords::assertCanSeeTableRecords()
         * @param \Illuminate\Support\Collection|array $records
         * @param bool $inOrder
         * @return static 
         * @static 
         */
        public static function assertCanSeeTableRecords($records, $inOrder = false)
        {
            return \Livewire\Features\SupportTesting\Testable::assertCanSeeTableRecords($records, $inOrder);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsRecords::assertCanNotSeeTableRecords()
         * @param \Illuminate\Support\Collection|array $records
         * @return static 
         * @static 
         */
        public static function assertCanNotSeeTableRecords($records)
        {
            return \Livewire\Features\SupportTesting\Testable::assertCanNotSeeTableRecords($records);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsRecords::assertCountTableRecords()
         * @param int $count
         * @return static 
         * @static 
         */
        public static function assertCountTableRecords($count)
        {
            return \Livewire\Features\SupportTesting\Testable::assertCountTableRecords($count);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsRecords::loadTable()
         * @return static 
         * @static 
         */
        public static function loadTable()
        {
            return \Livewire\Features\SupportTesting\Testable::loadTable();
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsSummaries::assertTableColumnSummarySet()
         * @param string $columnName
         * @param string $summarizerId
         * @param mixed $state
         * @param bool $isCurrentPaginationPageOnly
         * @return static 
         * @static 
         */
        public static function assertTableColumnSummarySet($columnName, $summarizerId, $state, $isCurrentPaginationPageOnly = false)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnSummarySet($columnName, $summarizerId, $state, $isCurrentPaginationPageOnly);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsSummaries::assertTableColumnSummaryNotSet()
         * @param string $columnName
         * @param string $summarizerId
         * @param mixed $state
         * @param bool $isCurrentPaginationPageOnly
         * @return static 
         * @static 
         */
        public static function assertTableColumnSummaryNotSet($columnName, $summarizerId, $state, $isCurrentPaginationPageOnly = false)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnSummaryNotSet($columnName, $summarizerId, $state, $isCurrentPaginationPageOnly);
        }

        /**
         * 
         *
         * @see \Filament\Tables\Testing\TestsSummaries::assertTableColumnSummarizerExists()
         * @param string $columnName
         * @param string $summarizerId
         * @return static 
         * @static 
         */
        public static function assertTableColumnSummarizerExists($columnName, $summarizerId)
        {
            return \Livewire\Features\SupportTesting\Testable::assertTableColumnSummarizerExists($columnName, $summarizerId);
        }

            }
    }

namespace Illuminate\View {
    /**
     * 
     *
     */
    class ComponentAttributeBag {
        /**
         * 
         *
         * @see \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::provide()
         * @param mixed $name
         * @static 
         */
        public static function wire($name)
        {
            return \Illuminate\View\ComponentAttributeBag::wire($name);
        }

            }
    /**
     * 
     *
     */
    class View {
        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $data
         * @static 
         */
        public static function layoutData($data = [])
        {
            return \Illuminate\View\View::layoutData($data);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $section
         * @static 
         */
        public static function section($section)
        {
            return \Illuminate\View\View::section($section);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $title
         * @static 
         */
        public static function title($title)
        {
            return \Illuminate\View\View::title($title);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $slot
         * @static 
         */
        public static function slot($slot)
        {
            return \Illuminate\View\View::slot($slot);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static 
         */
        public static function extends($view, $params = [])
        {
            return \Illuminate\View\View::extends($view, $params);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static 
         */
        public static function layout($view, $params = [])
        {
            return \Illuminate\View\View::layout($view, $params);
        }

        /**
         * 
         *
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param callable $callback
         * @static 
         */
        public static function response($callback)
        {
            return \Illuminate\View\View::response($callback);
        }

            }
    }


namespace  {
    class TPaySignatureValidator extends \App\Facades\TPaySignatureValidatorFacade {}
    class EloquentSerialize extends \AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade {}
    class L5Swagger extends \L5Swagger\L5SwaggerFacade {}
    class Livewire extends \Livewire\Livewire {}
}


namespace Facades\Livewire\Features\SupportFileUploads {
    /**
     * @mixin \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl     */
    class GenerateSignedUploadUrl extends \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl {}
}



