<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection binds
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection peripherals_id
     * @property Grid\Column|Collection releases_id
     * @property Grid\Column|Collection chips_id
     * @property Grid\Column|Collection solutions_id
     * @property Grid\Column|Collection statuses_id
     * @property Grid\Column|Collection class
     * @property Grid\Column|Collection comment
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection arch
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection abbr
     * @property Grid\Column|Collection release_date
     * @property Grid\Column|Collection eosl_date
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection industries_id
     * @property Grid\Column|Collection parent
     * @property Grid\Column|Collection details
     * @property Grid\Column|Collection source
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection alias
     * @property Grid\Column|Collection manufactors_id
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection types_id
     * @property Grid\Column|Collection kernel_version
     * @property Grid\Column|Collection crossover_version
     * @property Grid\Column|Collection box86_version
     * @property Grid\Column|Collection bd
     * @property Grid\Column|Collection am
     * @property Grid\Column|Collection tsm
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection softwares_id
     * @property Grid\Column|Collection os_subversion
     * @property Grid\Column|Collection adapt_source
     * @property Grid\Column|Collection adapted_before
     * @property Grid\Column|Collection admin_users_id
     * @property Grid\Column|Collection softname
     * @property Grid\Column|Collection solution
     * @property Grid\Column|Collection adaption_type
     * @property Grid\Column|Collection test_type
     * @property Grid\Column|Collection kylineco
     * @property Grid\Column|Collection appstore
     * @property Grid\Column|Collection iscert
     * @property Grid\Column|Collection path
     * @property Grid\Column|Collection specifications_id
     * @property Grid\Column|Collection brands_id
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection isconnected
     * @property Grid\Column|Collection isrequired
     * @property Grid\Column|Collection email_verified_at
     * @property Grid\Column|Collection is_enabled
     *
     * @method Grid\Column|Collection binds(string $label = null)
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection peripherals_id(string $label = null)
     * @method Grid\Column|Collection releases_id(string $label = null)
     * @method Grid\Column|Collection chips_id(string $label = null)
     * @method Grid\Column|Collection solutions_id(string $label = null)
     * @method Grid\Column|Collection statuses_id(string $label = null)
     * @method Grid\Column|Collection class(string $label = null)
     * @method Grid\Column|Collection comment(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection arch(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection abbr(string $label = null)
     * @method Grid\Column|Collection release_date(string $label = null)
     * @method Grid\Column|Collection eosl_date(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection industries_id(string $label = null)
     * @method Grid\Column|Collection parent(string $label = null)
     * @method Grid\Column|Collection details(string $label = null)
     * @method Grid\Column|Collection source(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection alias(string $label = null)
     * @method Grid\Column|Collection manufactors_id(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection types_id(string $label = null)
     * @method Grid\Column|Collection kernel_version(string $label = null)
     * @method Grid\Column|Collection crossover_version(string $label = null)
     * @method Grid\Column|Collection box86_version(string $label = null)
     * @method Grid\Column|Collection bd(string $label = null)
     * @method Grid\Column|Collection am(string $label = null)
     * @method Grid\Column|Collection tsm(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection softwares_id(string $label = null)
     * @method Grid\Column|Collection os_subversion(string $label = null)
     * @method Grid\Column|Collection adapt_source(string $label = null)
     * @method Grid\Column|Collection adapted_before(string $label = null)
     * @method Grid\Column|Collection admin_users_id(string $label = null)
     * @method Grid\Column|Collection softname(string $label = null)
     * @method Grid\Column|Collection solution(string $label = null)
     * @method Grid\Column|Collection adaption_type(string $label = null)
     * @method Grid\Column|Collection test_type(string $label = null)
     * @method Grid\Column|Collection kylineco(string $label = null)
     * @method Grid\Column|Collection appstore(string $label = null)
     * @method Grid\Column|Collection iscert(string $label = null)
     * @method Grid\Column|Collection path(string $label = null)
     * @method Grid\Column|Collection specifications_id(string $label = null)
     * @method Grid\Column|Collection brands_id(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection isconnected(string $label = null)
     * @method Grid\Column|Collection isrequired(string $label = null)
     * @method Grid\Column|Collection email_verified_at(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection binds
     * @property Show\Field|Collection id
     * @property Show\Field|Collection peripherals_id
     * @property Show\Field|Collection releases_id
     * @property Show\Field|Collection chips_id
     * @property Show\Field|Collection solutions_id
     * @property Show\Field|Collection statuses_id
     * @property Show\Field|Collection class
     * @property Show\Field|Collection comment
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection name
     * @property Show\Field|Collection arch
     * @property Show\Field|Collection email
     * @property Show\Field|Collection token
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection abbr
     * @property Show\Field|Collection release_date
     * @property Show\Field|Collection eosl_date
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection value
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection industries_id
     * @property Show\Field|Collection parent
     * @property Show\Field|Collection details
     * @property Show\Field|Collection source
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection order
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection alias
     * @property Show\Field|Collection manufactors_id
     * @property Show\Field|Collection version
     * @property Show\Field|Collection types_id
     * @property Show\Field|Collection kernel_version
     * @property Show\Field|Collection crossover_version
     * @property Show\Field|Collection box86_version
     * @property Show\Field|Collection bd
     * @property Show\Field|Collection am
     * @property Show\Field|Collection tsm
     * @property Show\Field|Collection type
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection softwares_id
     * @property Show\Field|Collection os_subversion
     * @property Show\Field|Collection adapt_source
     * @property Show\Field|Collection adapted_before
     * @property Show\Field|Collection admin_users_id
     * @property Show\Field|Collection softname
     * @property Show\Field|Collection solution
     * @property Show\Field|Collection adaption_type
     * @property Show\Field|Collection test_type
     * @property Show\Field|Collection kylineco
     * @property Show\Field|Collection appstore
     * @property Show\Field|Collection iscert
     * @property Show\Field|Collection path
     * @property Show\Field|Collection specifications_id
     * @property Show\Field|Collection brands_id
     * @property Show\Field|Collection username
     * @property Show\Field|Collection password
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection isconnected
     * @property Show\Field|Collection isrequired
     * @property Show\Field|Collection email_verified_at
     * @property Show\Field|Collection is_enabled
     *
     * @method Show\Field|Collection binds(string $label = null)
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection peripherals_id(string $label = null)
     * @method Show\Field|Collection releases_id(string $label = null)
     * @method Show\Field|Collection chips_id(string $label = null)
     * @method Show\Field|Collection solutions_id(string $label = null)
     * @method Show\Field|Collection statuses_id(string $label = null)
     * @method Show\Field|Collection class(string $label = null)
     * @method Show\Field|Collection comment(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection arch(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection abbr(string $label = null)
     * @method Show\Field|Collection release_date(string $label = null)
     * @method Show\Field|Collection eosl_date(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection industries_id(string $label = null)
     * @method Show\Field|Collection parent(string $label = null)
     * @method Show\Field|Collection details(string $label = null)
     * @method Show\Field|Collection source(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection alias(string $label = null)
     * @method Show\Field|Collection manufactors_id(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection types_id(string $label = null)
     * @method Show\Field|Collection kernel_version(string $label = null)
     * @method Show\Field|Collection crossover_version(string $label = null)
     * @method Show\Field|Collection box86_version(string $label = null)
     * @method Show\Field|Collection bd(string $label = null)
     * @method Show\Field|Collection am(string $label = null)
     * @method Show\Field|Collection tsm(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection softwares_id(string $label = null)
     * @method Show\Field|Collection os_subversion(string $label = null)
     * @method Show\Field|Collection adapt_source(string $label = null)
     * @method Show\Field|Collection adapted_before(string $label = null)
     * @method Show\Field|Collection admin_users_id(string $label = null)
     * @method Show\Field|Collection softname(string $label = null)
     * @method Show\Field|Collection solution(string $label = null)
     * @method Show\Field|Collection adaption_type(string $label = null)
     * @method Show\Field|Collection test_type(string $label = null)
     * @method Show\Field|Collection kylineco(string $label = null)
     * @method Show\Field|Collection appstore(string $label = null)
     * @method Show\Field|Collection iscert(string $label = null)
     * @method Show\Field|Collection path(string $label = null)
     * @method Show\Field|Collection specifications_id(string $label = null)
     * @method Show\Field|Collection brands_id(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection isconnected(string $label = null)
     * @method Show\Field|Collection isrequired(string $label = null)
     * @method Show\Field|Collection email_verified_at(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     */
    class Show {}

    /**
     
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
