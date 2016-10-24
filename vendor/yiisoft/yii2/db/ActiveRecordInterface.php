<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

/**
 * ActiveRecordInterface
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
interface ActiveRecordInterface
{
    /**
     * Returns the primary key **name(s)** for this AR class.
     * 返回该活动记录类的主键名称
     *
     * Note that an array should be returned even when the record only has a single primary key.
     * 请注意，就算该记录只有一个主键，也会返回一个数组。
     *
     * For the primary key **value** see [[getPrimaryKey()]] instead.
     * 主键值请参考[[getPrimaryKey()]]
     *
     * @return string[] the primary key name(s) for this AR class.
     * 返回值 该活动记录类的主键名
     */
    public static function primaryKey();

    /**
     * Returns the list of all attribute names of the record.
     * 返回该记录的所有属性名
     * @return array list of attribute names.
     * 返回值 数组 属性名列表
     */
    public function attributes();

    /**
     * Returns the named attribute value.
     * 返回指定属性值
     * If this record is the result of a query and the attribute is not loaded,
     * `null` will be returned.
     * 如果该记录是一个查询生成的，并且属性没有被加载，就会返回null
     * @param string $name the attribute name
     * 参数 字符串 属性名
     * @return mixed the attribute value. `null` if the attribute is not set or does not exist.
     * 返回值 混合型 属性值，如果属性没有被设置或者不存在就会返回null
     * @see hasAttribute()
     */
    public function getAttribute($name);

    /**
     * Sets the named attribute value.
     * 设置给定的属性值
     * @param string $name the attribute name.
     * 参数 字符串 属性名
     * @param mixed $value the attribute value.
     * 参数 混合型 属性值
     * @see hasAttribute()
     */
    public function setAttribute($name, $value);

    /**
     * Returns a value indicating whether the record has an attribute with the specified name.
     * 返回代表该记录是否有用给定名称的属性的值。
     * @param string $name the name of the attribute
     * 参数 字符串 属性名
     * @return boolean whether the record has an attribute with the specified name.
     * 返回值 boolean 记录是否拥有给定名称的属性
     */
    public function hasAttribute($name);

    /**
     * Returns the primary key value(s).
     * 返回主键的值
     * @param boolean $asArray whether to return the primary key value as an array. If true,
     * the return value will be an array with attribute names as keys and attribute values as values.
     * Note that for composite primary keys, an array will always be returned regardless of this parameter value.
     * 参数 boolean 是否把主键的值当做数组返回。如果为true，就会返回属性名为键，属性值为值的数组。
     * 请注意对于复合主键，就会忽略该参数的值，并且始终返回数组
     * @return mixed the primary key value. An array (attribute name => attribute value) is returned if the primary key
     * is composite or `$asArray` is true. A string is returned otherwise (`null` will be returned if
     * the key value is `null`).
     * 返回值 混合型 主键的值。如果是复合主键或者$asArray为true，返回一个数组(属性名=>属性值)。否则（如果主键值是null就返回null）就返回一个字符串
     */
    public function getPrimaryKey($asArray = false);

    /**
     * Returns the old primary key value(s).
     * 返回原先的主键值。
     * This refers to the primary key value that is populated into the record
     * after executing a find method (e.g. find(), findOne()).
     * 这个是指执行过find(例如find，findOne)方法以后被填充的主键值。
     * The value remains unchanged even if the primary key attribute is manually assigned with a different value.
     * 就算主键属性被手动分配了一个不同的值，该值也不会改变。
     * @param boolean $asArray whether to return the primary key value as an array. If true,
     * the return value will be an array with column name as key and column value as value.
     * 参数 boolean 是否把主键值当做数组返回。如果为true，返回值就是列名作为键，列属性作为值的数组。
     * If this is `false` (default), a scalar value will be returned for non-composite primary key.
     * 如果为false（默认），不是复合主键的情况会返回一个标量值
     * @property mixed The old primary key value. An array (column name => column value) is
     * returned if the primary key is composite. A string is returned otherwise (`null` will be
     * returned if the key value is `null`).
     * 属性 混合型 原先的主键值。当是复合主键时，返回数组（列名=>列值）。否者就返回字符串（如果主键值是null，就返回null）
     * @return mixed the old primary key value. An array (column name => column value) is returned if the primary key
     * is composite or `$asArray` is true. A string is returned otherwise (`null` will be returned if
     * the key value is `null`).
     * 返回值 混合型 原先的主键值，当复合主键或$asArray为true的时候，返回数组（列名=>列值）。否者就返回字符串（如果主键值是null，就返回null）
     */
    public function getOldPrimaryKey($asArray = false);

    /**
     * Returns a value indicating whether the given set of attributes represents the primary key for this model
     * 返回代表给定属性是否是该模型的主键值的值。
     * @param array $keys the set of attributes to check
     * 参数 数组 需要检测的属性集合
     * @return boolean whether the given set of attributes represents the primary key for this model
     * 返回值 boolean 给定的属性集合是否代表该模型的主键
     */
    public static function isPrimaryKey($keys);

    /**
     * Creates an [[ActiveQueryInterface]] instance for query purpose.
     * 为查询创建一个活动查询接口实例
     *
     * The returned [[ActiveQueryInterface]] instance can be further customized by calling
     * methods defined in [[ActiveQueryInterface]] before `one()` or `all()` is called to return
     * populated ActiveRecord instances. For example,
     * 返回的[[ActiveQueryInterface]]实例可以通过在one或all方法之前调用在[[ActiveQueryInterface]]定义的方法，填充活动记录的实例。例如，
     *
     * ```php
     * // find the customer whose ID is 1
     * // 查询ID为1的客户
     * $customer = Customer::find()->where(['id' => 1])->one();
     *
     * // find all active customers and order them by their age:
     * // 查询所有激活的用户，并按照他们的年龄排序
     * $customers = Customer::find()
     *     ->where(['status' => 1])
     *     ->orderBy('age')
     *     ->all();
     * ```
     *
     * This method is also called by [[BaseActiveRecord::hasOne()]] and [[BaseActiveRecord::hasMany()]] to
     * create a relational query.
     * 该方法也会被[[BaseActiveRecord::hasOne()]] 和 [[BaseActiveRecord::hasMany()]]调用去创建一个关联查询
     *
     * You may override this method to return a customized query. For example,
     * 你可以重写此方法，返回一个自定义的查询，例如，
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         // use CustomerQuery instead of the default ActiveQuery
     *         // 使用自定义的查询代替默认的活动查询
     *         return new CustomerQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * The following code shows how to apply a default condition for all queries:
     * 下边的代码展示了如何为所有的查询应用默认条件：
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->where(['deleted' => false]);
     *     }
     * }
     *
     * // Use andWhere()/orWhere() to apply the default condition
     * // 使用andWhere/orWhere方法添加默认的条件
     * // SELECT FROM customer WHERE `deleted`=:deleted AND age>30
     * // 这个是一个sql语句
     * $customers = Customer::find()->andWhere('age>30')->all();
     *
     * // Use where() to ignore the default condition
     * // 使用where方法忽略默认的条件
     * // SELECT FROM customer WHERE age>30
     * $customers = Customer::find()->where('age>30')->all();
     *
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface]] instance.
     * 返回值 新创建的[[ActiveQueryInterface]]实例
     */
    public static function find();

    /**
     * Returns a single active record model instance by a primary key or an array of column values.
     * 根据主键值或者数组的列返回一个单一的活动记录模型实例。
     *
     * The method accepts:
     * 该方法接受：
     *
     *  - a scalar value (integer or string): query by a single primary key value and return the
     *    corresponding record (or `null` if not found).
     *  - 一个标量值（整型或者字符串）：通过一个主键查询并返回相应的记录（如果没有就返回null）
     *  - a non-associative array: query by a list of primary key values and return the
     *    first record (or `null` if not found).
     *  - 一个非关联数组：根据一些主键值查询，并返回第一条记录（如果没有就返回null）
     *  - an associative array of name-value pairs: query by a set of attribute values and return a single record
     *    matching all of them (or `null` if not found). Note that `['id' => 1, 2]` is treated as a non-associative array.
     *  - 一个关联数组的键值对：根据一些属性值查询，并返回一个匹配所有的记录（如果没有返回null）。注意['id' => 1, 2]会被当做非关联数组。
     *
     * That this method will automatically call the `one()` method and return an [[ActiveRecordInterface|ActiveRecord]]
     * instance. For example,
     * 该方法会自动调用one方法并返回一个[[ActiveRecordInterface|ActiveRecord]]实例。例如，
     *
     * ```php
     * // find a single customer whose primary key value is 10
     * // 查找主键值是10的一个客户
     * $customer = Customer::findOne(10);
     *
     * // the above code is equivalent to:
     * // 上边的代码等价于：
     * $customer = Customer::find()->where(['id' => 10])->one();
     *
     * // find the first customer whose age is 30 and whose status is 1
     * // 找到第一个年龄为30，状态为1的客户
     * $customer = Customer::findOne(['age' => 30, 'status' => 1]);
     *
     * // the above code is equivalent to:
     * // 上边的代码等价于：
     * $customer = Customer::find()->where(['age' => 30, 'status' => 1])->one();
     * ```
     *
     * @param mixed $condition primary key value or a set of column values
     * 参数 混合型 主键值或列值
     * @return static ActiveRecord instance matching the condition, or `null` if nothing matches.
     * 返回值 复合条件的活动记录实例，如果没有匹配就是null
     */
    public static function findOne($condition);

    /**
     * Returns a list of active record models that match the specified primary key value(s) or a set of column values.
     * 返回匹配指定主键或一系列列值一系列活动记录模型
     *
     * The method accepts:
     * 该方法接受：
     *
     *  - a scalar value (integer or string): query by a single primary key value and return an array containing the
     *    corresponding record (or an empty array if not found).
     *  - 一个标量值（整型或者字符串）：根据一个主键值的查询，并返回一个包含相应记录的数组（如果没有就返回空数组）
     *  - a non-associative array: query by a list of primary key values and return the
     *    corresponding records (or an empty array if none was found).
     *    Note that an empty condition will result in an empty result as it will be interpreted as a search for
     *    primary keys and not an empty `WHERE` condition.
     *  - 一个非关联数组：根据一些列主键值的查询并返回相应的记录（如果没有就返回空数组）
     *  - an associative array of name-value pairs: query by a set of attribute values and return an array of records
     *    matching all of them (or an empty array if none was found). Note that `['id' => 1, 2]` is treated as
     *    a non-associative array.
     *  - 一个键值对组成的关联数组：根据一些列属性值查询并返回一个匹配所有的记录组成的数组（如果没有返回空数组）。请注意 ['id' => 1, 2]被当做非关联数组
     *
     * This method will automatically call the `all()` method and return an array of [[ActiveRecordInterface|ActiveRecord]]
     * instances. For example,
     * 该方法会自动调用all方法，并返回一个[[ActiveRecordInterface|ActiveRecord]]实例组成的数组。例如：
     *
     * ```php
     * // find the customers whose primary key value is 10
     * // 查找主键id为10的客户
     * $customers = Customer::findAll(10);
     *
     * // the above code is equivalent to:
     * // 上边的代码等价于
     * $customers = Customer::find()->where(['id' => 10])->all();
     *
     * // find the customers whose primary key value is 10, 11 or 12.
     * // 查找主键值为10,11,12的客户
     * $customers = Customer::findAll([10, 11, 12]);
     *
     * // the above code is equivalent to:
     * // 上边的代码等价于
     * $customers = Customer::find()->where(['id' => [10, 11, 12]])->all();
     *
     * // find customers whose age is 30 and whose status is 1
     * // 查找年龄为30，状态为1的客户
     * $customers = Customer::findAll(['age' => 30, 'status' => 1]);
     *
     * // the above code is equivalent to:
     * // 上边的代码等价于：
     * $customers = Customer::find()->where(['age' => 30, 'status' => 1])->all();
     * ```
     *
     * @param mixed $condition primary key value or a set of column values
     * 参数 混合型 主键值或者列值
     * @return array an array of ActiveRecord instance, or an empty array if nothing matches.
     * 返回值 数组 活动记录实例组成的数组，没有匹配的时候返回空数组
     */
    public static function findAll($condition);

    /**
     * Updates records using the provided attribute values and conditions.
     * 根据给定的属性值和条件更新记录。
     * For example, to change the status to be 1 for all customers whose status is 2:
     * 例如，把所有状态为2的用户的状态改为1
     *
     * ```php
     * Customer::updateAll(['status' => 1], ['status' => '2']);
     * ```
     *
     * @param array $attributes attribute values (name-value pairs) to be saved for the record.
     * Unlike [[update()]] these are not going to be validated.
     * 参数 数组 该记录保存的属性值（键值对），跟update方法不同，这些属性不会被验证。
     * @param array $condition the condition that matches the records that should get updated.
     * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
     * 参数 数组 条件 满足条件的记录会被更新。关于如何指定该参数请参考[[QueryInterface::where()]]
     * An empty condition will match all records.
     * 空数组会匹配所有记录
     * @return integer the number of rows updated
     * 返回值 这些 被更新的行数
     */
    public static function updateAll($attributes, $condition = null);

    /**
     * Deletes records using the provided conditions.
     * 使用给定的条件删除记录
     * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
     * 警告：如果不指定条件，该方法会删除数据表里边的所有行。
     *
     * For example, to delete all customers whose status is 3:
     * 例如，删除状态为3的客户：
     *
     * ```php
     * Customer::deleteAll([status = 3]);
     * ```
     *
     * @param array $condition the condition that matches the records that should get deleted.
     * Please refer to [[QueryInterface::where()]] on how to specify this parameter.
     * 参数 数组 符合该条件的记录应该被删除。关于如何指定该参数，请参考[[QueryInterface::where()]]
     * An empty condition will match all records.
     * 空条件会匹配所有记录
     * @return integer the number of rows deleted
     * 返回值 被删除的行数
     */
    public static function deleteAll($condition = null);

    /**
     * Saves the current record.
     * 保存当前记录
     *
     * This method will call [[insert()]] when [[getIsNewRecord()|isNewRecord]] is true, or [[update()]]
     * when [[getIsNewRecord()|isNewRecord]] is false.
     * 当[[getIsNewRecord()|isNewRecord]]为true的时候，该方法会调用insert方法，否则，调用update方法。
     *
     * For example, to save a customer record:
     * 例如，保存客户记录：
     *
     * ```php
     * $customer = new Customer; // or $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->save();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[Model::validate()|validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * 参数 boolean 保存数据前是否执行验证（调用[[Model::validate()|validate()]]方法）。默认是true。如果验证失败，数据不会保存到数据库
     * 并且该方法会返回false
     * @param array $attributeNames list of attribute names that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性名。默认是null，表示所有从数据库里加载的属性都会被保存。
     * @return boolean whether the saving succeeded (i.e. no validation errors occurred).
     * 返回值 boolean 保存是否成功（例如，没有验证错误发生）
     */
    public function save($runValidation = true, $attributeNames = null);

    /**
     * Inserts the record into the database using the attribute values of this record.
     * 把该记录的属性值插入到数据库
     *
     * Usage example:
     * 实例：
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[Model::validate()|validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * 参数 boolean 保存记录之前是否执行验证（调用[[Model::validate()|validate()]]）。默认是true。如果验证失败，记录就不会保存到数据库，
     * 并且该方法会返回false
     *
     * @param array $attributes list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性组成的数组。默认是null，表示所有从数据库加载的属性都会被保存。
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     * 返回值 boolean 属性是否合法以及记录是否成功插入
     */
    public function insert($runValidation = true, $attributes = null);

    /**
     * Saves the changes to this active record into the database.
     * 把当前活动记录的改变保存到数据库
     *
     * Usage example:
     * 使用举例
     *
     * ```php
     * $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->update();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[Model::validate()|validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * 参数 boolean 保存记录之前是否执行验证（调用[[Model::validate()|validate()]]）。默认是true。如果验证失败，记录就不会保存到数据库，
     * 并且该方法会返回false
     *
     * @param array $attributeNames list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性组成的数组。默认是null，表示所有从数据库加载的属性都会被保存。
     *
     * @return integer|boolean the number of rows affected, or `false` if validation fails
     * or updating process is stopped for other reasons.
     * 返回值 整型|booleann 受影响的行数，如果更新失败或者数据不合法就会返回false
     * Note that it is possible that the number of rows affected is 0, even though the
     * update execution is successful.
     * 请注意，就算更新操作执行成功，受影响的行数也可能是0
     */
    public function update($runValidation = true, $attributeNames = null);

    /**
     * Deletes the record from the database.
     * 从数据库中删除记录
     *
     * @return integer|boolean the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible that the number of rows deleted is 0, even though the deletion execution is successful.
     * 返回值 整型|boolean 被删除的行数，当删除失败的时候会返回false。
     * 请注意，就算删除操作执行成功，返回的受影响行数也有可能为0
     */
    public function delete();

    /**
     * Returns a value indicating whether the current record is new (not saved in the database).
     * 返回一个代表当前记录是不是新增的记录（没有被保存到数据库）
     * @return boolean whether the record is new and should be inserted when calling [[save()]].
     * 返回值 boolean 记录是否新增，调用save方式时是否需要执行插入操作
     */
    public function getIsNewRecord();

    /**
     * Returns a value indicating whether the given active record is the same as the current one.
     * 返回一个能表示给定的活动记录和当前的活动记录是否是相同的boolean值
     * Two [[getIsNewRecord()|new]] records are considered to be not equal.
     * 两个由[[getIsNewRecord()|new]]创建的记录被认为是不相同的
     * @param static $record record to compare to
     * 参数 要对比的记录
     * @return boolean whether the two active records refer to the same row in the same database table.
     * 返回值 boolean 两个活动记录是否指向同一数据表中的相同行
     */
    public function equals($record);

    /**
     * Returns the relation object with the specified name.
     * 返回指定名称的关系对象
     * A relation is defined by a getter method which returns an object implementing the [[ActiveQueryInterface]]
     * (normally this would be a relational [[ActiveQuery]] object).
     * 关系通过getter方法定义，返回一个实现[[ActiveQueryInterface]]接口的对象（正常情况下，这是一个关系[[ActiveQuery]]对象）
     * It can be declared in either the ActiveRecord class itself or one of its behaviors.
     * 它可以生命在活动记录类中，或者它的行为中
     * @param string $name the relation name
     * 参数 字符串 关系名称
     * @param boolean $throwException whether to throw exception if the relation does not exist.
     * 参数 boolean 当关联关系不存在时，是否抛出异常
     * @return ActiveQueryInterface the relational query object
     * 返回值 关联查询对象
     */
    public function getRelation($name, $throwException = true);

    /**
     * Populates the named relation with the related records.
     * 使用相关的记录填充给定的关联。
     * Note that this method does not check if the relation exists or not.
     * 注意，该方法不会检测关联是否存在。
     * @param string $name the relation name (case-sensitive)
     * 参数 字符串 关联名称（大小写区分）
     * @param ActiveRecordInterface|array|null $records the related records to be populated into the relation.
     * 参数 被填充到关联关系的关联记录。
     * @since 2.0.8
     */
    public function populateRelation($name, $records);

    /**
     * Establishes the relationship between two records.
     * 建立两个记录的关联关系
     *
     * The relationship is established by setting the foreign key value(s) in one record
     * to be the corresponding primary key value(s) in the other record.
     * The record with the foreign key will be saved into database without performing validation.
     * 关联关系通过设置记录里边的外键跟相应的另外一个记录的主键关联而建立的。
     * 带有外键的记录将会不执行验证就保存到数据库。
     *
     * If the relationship involves a junction table, a new row will be inserted into the
     * junction table which contains the primary key values from both records.
     * 如果关联关系包含中间表，一个新行会被插入到包含双方记录的中间表中。
     *
     * This method requires that the primary key value is not `null`.
     * 该方法要求主键值不能为null
     *
     * @param string $name the case sensitive name of the relationship.
     * 参数 字符串 大小写区分的关联关系。
     * @param static $model the record to be linked with the current one.
     * 参数 被关联到当前模型的模型
     * @param array $extraColumns additional column values to be saved into the junction table.
     * This parameter is only meaningful for a relationship involving a junction table
     * (i.e., a relation set with [[ActiveQueryInterface::via()]]).
     * 参数 连接表中需要额外保存的列值。该参数只有在关联关系中包含中间表采有意义（例如，使用ActiveQueryInterface::via()方法设置的关联关系）
     */
    public function link($name, $model, $extraColumns = []);

    /**
     * Destroys the relationship between two records.
     * 删除两个记录的关联关系
     *
     * The record with the foreign key of the relationship will be deleted if `$delete` is true.
     * 当$delete为true的时候，拥有外键的记录的关联将会被删除。
     * Otherwise, the foreign key will be set `null` and the record will be saved without validation.
     * 否则，外键将会被设置为null，记录会不经过验证就保存。
     *
     *
     * @param string $name the case sensitive name of the relationship.
     * 参数 字符串 区分大小写的关联关系。
     * @param static $model the model to be unlinked from the current one.
     * 参数 将要被从当前模型删除的模型
     * @param boolean $delete whether to delete the model that contains the foreign key.
     * 参数 boolean 是否删除包含外键的模型
     * If false, the model's foreign key will be set `null` and saved.
     * 如果为false，该模型的外键会被设置为null并保存
     * If true, the model containing the foreign key will be deleted.
     * 如果为true，包含外键的模型会被删除。
     */
    public function unlink($name, $model, $delete = false);

    /**
     * Returns the connection used by this AR class.
     * 返回活动记录类使用的数据库连接
     * @return mixed the database connection used by this AR class.
     * 返回值 混合型 活动记录类使用的数据库连接
     */
    public static function getDb();
}
