<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 * ActiveRecord 是表示关系数据对象类的基类
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * 活动记录实现[活动记录设计模式]
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * 活动记录的前提是一个独立的对象关联数据表中一个指定的行。对象的属性被映射成相关表的列。
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 * 引用一个活动记录的属性相当于访问相应表里边的记录对应的列
 *
 * As an example, say that the `Customer` ActiveRecord class is associated with the `customer` table.
 * 举例来说，Customer活动记录类跟customer表关联
 * This would mean that the class's `name` attribute is automatically mapped to the `name` column in `customer` table.
 * 这意味这类的name属性自动映射到customer表里边的name列
 * Thanks to Active Record, assuming the variable `$customer` is an object of type `Customer`, to get the value of
 * the `name` column for the table row, you can use the expression `$customer->name`.
 * 感谢活动记录，假设变量$customer是Customer的一个对象，要获取表里边name列的属性值，你可以使用$customer->name。
 * In this example, Active Record is providing an object-oriented interface for accessing data stored in the database.
 * But Active Record provides much more functionality than this.
 * 在这个例子中，活动记录提供了一个面向对象的接口来访问存储在数据库中的数据。但是活动记录提供的功能要远比例子丰富。
 *
 * To declare an ActiveRecord class you need to extend [[\yii\db\ActiveRecord]] and
 * implement the `tableName` method:
 * 要声明活动记录类，需要继承[[\yii\db\ActiveRecord]]，并实现tableName方法：
 *
 * ```php
 * <?php
 *
 * class Customer extends \yii\db\ActiveRecord
 * {
 *     public static function tableName()
 *     {
 *         return 'customer';
 *     }
 * }
 * ```
 *
 * The `tableName` method only has to return the name of the database table associated with the class.
 * tableName方法只需要返回跟类相关的数据表的名称
 *
 * > Tip: You may also use the [Gii code generator](guide:start-gii) to generate ActiveRecord classes from your
 * > database tables.
 * > 提示：你也可以使用Gii代码生成器，根据数据库表生成活动记录类
 *
 * Class instances are obtained in one of two ways:
 * 类的实例可以通过一下两种方式获取：
 *
 * * Using the `new` operator to create a new, empty object
 * * 使用new操作符创建一个新的空类
 * * Using a method to fetch an existing record (or records) from the database
 * * 使用方法从数据库里边获取已经存在的记录
 *
 * Below is an example showing some typical usage of ActiveRecord:
 * 下边的例子展示了活动记录的用法：
 *
 * ```php
 * $user = new User();
 * $user->name = 'Qiang';
 * $user->save();  // a new row is inserted into user table // 向用户表里边添加一行
 *
 * // the following will retrieve the user 'CeBe' from the database
 * // 下边的语句将会从数据库中检索用户名为CeBe的用户
 * $user = User::find()->where(['name' => 'CeBe'])->one();
 *
 * // this will get related records from orders table when relation is defined
 * // 当定义了关联关系以后，下面的语句将会从订单表中获取相关的记录
 * $orders = $user->orders;
 * ```
 *
 * For more details and usage information on ActiveRecord, see the [guide article on ActiveRecord](guide:db-active-record).
 * 想获取更多关于活动记录的详情和使用信息，请参考关于活动记录的手册
 *
 * @method ActiveQuery hasMany($class, array $link) see [[BaseActiveRecord::hasMany()]] for more info
 * 方法 参考[[BaseActiveRecord::hasMany()]]方法获取更多
 * @method ActiveQuery hasOne($class, array $link) see [[BaseActiveRecord::hasOne()]] for more info
 * 方法 参考[[BaseActiveRecord::hasOne()]]方法获取更多
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ActiveRecord extends BaseActiveRecord
{
    /**
     * The insert operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
     * 插入操作。主要用于重写[[transactions()]]时，指定哪种操作是事务性的。
     */
    const OP_INSERT = 0x01;
    /**
     * The update operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
     * 更新操作。主要用于重写[[transactions()]]时，指定哪种操作是事务性的
     */
    const OP_UPDATE = 0x02;
    /**
     * The delete operation. This is mainly used when overriding [[transactions()]] to specify which operations are transactional.
     * 删除操作。主要用于重写[[transactions()]]时，指定哪种操作是事务性的。
     */
    const OP_DELETE = 0x04;
    /**
     * All three operations: insert, update, delete.
     * 全部三种操作：插入，更新，删除
     * This is a shortcut of the expression: OP_INSERT | OP_UPDATE | OP_DELETE.
     * 是OP_INSERT | OP_UPDATE | OP_DELETE 这三种表达方式的简化形式。
     */
    const OP_ALL = 0x07;


    /**
     * Loads default values from database table schema
     * 从数据表中载入默认值
     *
     * You may call this method to load default values after creating a new instance:
     * 你可以在创建一个新实例以后调用该方法载入默认值：
     *
     * ```php
     * // class Customer extends \yii\db\ActiveRecord
     * // 类Customer继承\yii\db\ActiveRecord
     * $customer = new Customer();
     * $customer->loadDefaultValues();
     * ```
     *
     * @param boolean $skipIfSet whether existing value should be preserved.
     * 参数 boolean 是否保留已经存在的值
     * This will only set defaults for attributes that are `null`.
     * 只会设置属性为null的默认值
     * @return $this the model instance itself.
     * 返回值 模型实例自身
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        foreach (static::getTableSchema()->columns as $column) {
            if ($column->defaultValue !== null && (!$skipIfSet || $this->{$column->name} === null)) {
                $this->{$column->name} = $column->defaultValue;
            }
        }
        return $this;
    }

    /**
     * Returns the database connection used by this AR class.
     * 返回活动记录类使用的数据库连接
     * By default, the "db" application component is used as the database connection.
     * 默认情况下，db应用组件用于数据库连接
     * You may override this method if you want to use a different database connection.
     * 如果你需要一个不同的数据库连接的时候，你可以重写此方法
     * @return Connection the database connection used by this AR class.
     * 返回值 被活动记录类使用的数据库连接
     */
    public static function getDb()
    {
        return Yii::$app->getDb();
    }

    /**
     * Creates an [[ActiveQuery]] instance with a given SQL statement.
     * 使用给定的sql语句，创建一个活动查询实例
     *
     * Note that because the SQL statement is already specified, calling additional
     * query modification methods (such as `where()`, `order()`) on the created [[ActiveQuery]]
     * instance will have no effect. However, calling `with()`, `asArray()` or `indexBy()` is
     * still fine.
     * 请注意，因为sql语句已经指定，在已经创建了活动查询实例的对象上调用额外的查询修改方法（例如where，order）将会没有效果。
     * 但是调用with，asArray,indexBy还是有效的
     *
     * Below is an example:
     * 请看下边的例子：
     *
     * ```php
     * $customers = Customer::findBySql('SELECT * FROM customer')->all();
     * ```
     *
     * @param string $sql the SQL statement to be executed
     * 参数 字符串 将要执行的sql语句
     * @param array $params parameters to be bound to the SQL statement during execution.
     * 参数 数组 sql语句执行的时候绑定到该sql语句的参数
     * @return ActiveQuery the newly created [[ActiveQuery]] instance
     * 返回值 活动记录 新创建的活动查询实例
     */
    public static function findBySql($sql, $params = [])
    {
        $query = static::find();
        $query->sql = $sql;

        return $query->params($params);
    }

    /**
     * Finds ActiveRecord instance(s) by the given condition.
     * 使用给定的条件查找活动记录实例。
     * This method is internally called by [[findOne()]] and [[findAll()]].
     * 该方法会被findOne和findAll方法在内部调用。
     * @param mixed $condition please refer to [[findOne()]] for the explanation of this parameter
     * 参数 混合型 关于该参数的解释，请参考findOne方法
     * @return ActiveQueryInterface the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
     * 返回值 活动查询接口 新创建的活动查询接口（活动查询）实例
     * @throws InvalidConfigException if there is no primary key defined
     * 如果没有主键，抛出不合法的配置异常
     * @internal
     */
    protected static function findByCondition($condition)
    {
        $query = static::find();

        if (!ArrayHelper::isAssociative($condition)) {
            // query by primary key
            // 根据主键查询
            $primaryKey = static::primaryKey();
            if (isset($primaryKey[0])) {
                $pk = $primaryKey[0];
                if (!empty($query->join) || !empty($query->joinWith)) {
                    $pk = static::tableName() . '.' . $pk;
                }
                $condition = [$pk => $condition];
            } else {
                throw new InvalidConfigException('"' . get_called_class() . '" must have a primary key.');
            }
        }

        return $query->andWhere($condition);
    }

    /**
     * Updates the whole table using the provided attribute values and conditions.
     * 使用提供的属性值和条件更新整个表。
     * For example, to change the status to be 1 for all customers whose status is 2:
     * 例如，要把状态为2的客户属性全部改为1
     *
     * ```php
     * Customer::updateAll(['status' => 1], 'status = 2');
     * ```
     *
     * @param array $attributes attribute values (name-value pairs) to be saved into the table
     * 参数 数组 被保存到表中的属性值（键值对）
     * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * 参数 字符串|数组 将要放在更新语句where部分的条件
     * @param array $params the parameters (name => value) to be bound to the query.
     * 参数 数组 绑定到查询中的参数（键值对）
     * @return integer the number of rows updated
     * 返回值 整型 更新的行数
     */
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $attributes, $condition, $params);

        return $command->execute();
    }

    /**
     * Updates the whole table using the provided counter changes and conditions.
     * 使用给定的计数器改变和条件更新整张表
     * For example, to increment all customers' age by 1,
     * 例如，给所有的客户的年龄增加1，
     *
     * ```php
     * Customer::updateAllCounters(['age' => 1]);
     * ```
     *
     * @param array $counters the counters to be updated (attribute name => increment value).
     * 参数 数组 被更新的计数器（属性名=>增加值）
     * Use negative values if you want to decrement the counters.
     * 如果你想减少计数，可以使用负数
     * @param string|array $condition the conditions that will be put in the WHERE part of the UPDATE SQL.
     * 参数 字符串|数组 用于sql语句where部分的条件
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * 关于如何设置该参数，请参考[[Query::where()]]方法
     * @param array $params the parameters (name => value) to be bound to the query.
     * 参数 数组 绑定到查询语句的参数（键值对）
     * Do not name the parameters as `:bp0`, `:bp1`, etc., because they are used internally by this method.
     * 不要把参数命名为`:bp0`, `:bp1`,等，因为它们会在该方法内部使用
     * @return integer the number of rows updated
     */
    public static function updateAllCounters($counters, $condition = '', $params = [])
    {
        $n = 0;
        foreach ($counters as $name => $value) {
            $counters[$name] = new Expression("[[$name]]+:bp{$n}", [":bp{$n}" => $value]);
            $n++;
        }
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $counters, $condition, $params);

        return $command->execute();
    }

    /**
     * Deletes rows in the table using the provided conditions.
     * 使用给定的条件删除数据表里边的行。
     * WARNING: If you do not specify any condition, this method will delete ALL rows in the table.
     * 警告：如果你不指定任何条件，该方法会删除表里边的所有数据
     *
     * For example, to delete all customers whose status is 3:
     * 例如，删除状态为3的用户：
     *
     * ```php
     * Customer::deleteAll('status = 3');
     * ```
     *
     * @param string|array $condition the conditions that will be put in the WHERE part of the DELETE SQL.
     * 参数 字符串|数组 将要放到删除语句where部分的条件。
     * Please refer to [[Query::where()]] on how to specify this parameter.
     * 关于如何指定该参数，请参考[[Query::where()]]方法
     * @param array $params the parameters (name => value) to be bound to the query.
     * 参数 数组 绑定到查询的参数键值对
     * @return integer the number of rows deleted
     * 返回值 整型 被删除的行数
     */
    public static function deleteAll($condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->delete(static::tableName(), $condition, $params);

        return $command->execute();
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     * 返回值 活动查询 新创建的活动查询实例
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Declares the name of the database table associated with this AR class.
     * 声明跟活动记录类相关的数据表名称。
     * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
     * with prefix [[Connection::tablePrefix]]. For example if [[Connection::tablePrefix]] is `tbl_`,
     * `Customer` becomes `tbl_customer`, and `OrderItem` becomes `tbl_order_item`. You may override this method
     * if the table is not named after this convention.
     * 默认该方法通过调用[[Inflector::camel2id()]]方法和前缀[[Connection::tablePrefix]]返回类名作为表名称。例如假设[[Connection::tablePrefix]]
     * 是tbl_，Customer变成tbl_customer,OrderItem变成tbl_order_item。当表名跟默认的不一致的时候，你也可以重写此方法。
     * @return string the table name
     * 返回值 字符串 表名
     */
    public static function tableName()
    {
        return '{{%' . Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '}}';
    }

    /**
     * Returns the schema information of the DB table associated with this AR class.
     * 返回跟该活动记录类相关的数据表的概要信息
     * @return TableSchema the schema information of the DB table associated with this AR class.
     * 返回值 跟活动记录类相关的数据表概要信息
     * @throws InvalidConfigException if the table for the AR class does not exist.
     * 当跟活动记录相关的表不存在时，抛出不合法的配置异常
     */
    public static function getTableSchema()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * 返回该活动记录类的主键名。
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     * 默认会返回跟活动记录类相关的数据表中声明的主键。
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     * 如果数据表没有声明任何主键，你需要重写此方法，返回该活动记录类需要作为主键的属性。
     *
     * Note that an array should be returned even for a table with single primary key.
     * 注意，就算是表只有一个主键，也会返回一个数组。
     *
     * @return string[] the primary keys of the associated database table.
     * 返回值 数据表的主键。
     */
    public static function primaryKey()
    {
        return static::getTableSchema()->primaryKey;
    }

    /**
     * Returns the list of all attribute names of the model.
     * 返回模型的所有属性名的列表
     * The default implementation will return all column names of the table associated with this AR class.
     * 默认会返回所有跟活动记录类相关的数据表的所有列名
     * @return array list of attribute names.
     * 返回值 数组 属性名列表
     */
    public function attributes()
    {
        return array_keys(static::getTableSchema()->columns);
    }

    /**
     * Declares which DB operations should be performed within a transaction in different scenarios.
     * 声明在不同的场景下，哪种数据库操作应该采用事务处理。
     * The supported DB operations are: [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]],
     * which correspond to the [[insert()]], [[update()]] and [[delete()]] methods, respectively.
     * 支持的数据库操作是：[[OP_INSERT]], [[OP_UPDATE]] 和 [[OP_DELETE]],分别对应[[insert()]], [[update()]] 和 [[delete()]]
     * 方法
     * By default, these methods are NOT enclosed in a DB transaction.
     * 这些方法默认没有加上数据库事务操作
     *
     * In some scenarios, to ensure data consistency, you may want to enclose some or all of them
     * in transactions. You can do so by overriding this method and returning the operations
     * that need to be transactional. For example,
     * 在一些场景中，为了确保数据一致性，你需要把一些或全部操作放入事务。你可以通过重写此方法，并返回需要事务性处理的操作，例如：
     *
     * ```php
     * return [
     *     'admin' => self::OP_INSERT,
     *     'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
     *     // the above is equivalent to the following:
     *     // 上边的依据等价于下边的：
     *     // 'api' => self::OP_ALL,
     *
     * ];
     * ```
     *
     * The above declaration specifies that in the "admin" scenario, the insert operation ([[insert()]])
     * should be done in a transaction; and in the "api" scenario, all the operations should be done
     * in a transaction.
     * 上边的声明指定了在admin场景中，插入操作应该使用事务处理；在api场景中，所有的曹总都要使用事务完成。
     *
     * @return array the declarations of transactional operations. The array keys are scenarios names,
     * and the array values are the corresponding transaction operations.
     * 返回值 数组 需要进行事务处理的操作符的声明。数组的键是场景名称，数组的值是相应的事务操作。
     */
    public function transactions()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function populateRecord($record, $row)
    {
        $columns = static::getTableSchema()->columns;
        foreach ($row as $name => $value) {
            if (isset($columns[$name])) {
                $row[$name] = $columns[$name]->phpTypecast($value);
            }
        }
        parent::populateRecord($record, $row);
    }

    /**
     * Inserts a row into the associated database table using the attribute values of this record.
     * 把该记录的属性值插入到相关的数据表中。
     *
     * This method performs the following steps in order:
     * 该方法按照如下顺序执行：
     *
     * 1. call [[beforeValidate()]] when `$runValidation` is `true`. If [[beforeValidate()]]
     *    returns `false`, the rest of the steps will be skipped;
     * 1. 当$runValidation为true的时候，调用[[beforeValidate()]]方法。如果[[beforeValidate()]]返回false，后边的步骤将会被忽略
     * 2. call [[afterValidate()]] when `$runValidation` is `true`. If validation
     *    failed, the rest of the steps will be skipped;
     * 2. 当$runValidation为true时，调用[[afterValidate()]]方法。如果验证失败，后边的步骤将会被忽略
     * 3. call [[beforeSave()]]. If [[beforeSave()]] returns `false`,
     *    the rest of the steps will be skipped;
     * 3. 调用[[beforeSave()]]方法，如果[[beforeSave()]]方法返回false，后边的步骤会被忽略
     * 4. insert the record into database. If this fails, it will skip the rest of the steps;
     * 4. 把记录插入数据库。如果插入失败，剩余的步骤会被忽略
     * 5. call [[afterSave()]];
     * 5. 调用[[afterSave()]]方法；
     *
     * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
     * [[EVENT_AFTER_VALIDATE]], [[EVENT_BEFORE_INSERT]], and [[EVENT_AFTER_INSERT]]
     * will be raised by the corresponding methods.
     * 在上边步骤1,2,3和5中，事件[[EVENT_BEFORE_VALIDATE]],[[EVENT_AFTER_VALIDATE]], [[EVENT_BEFORE_INSERT]], 和 [[EVENT_AFTER_INSERT]]
     * 会被相应的方法触发
     *
     * Only the [[dirtyAttributes|changed attribute values]] will be inserted into database.
     * 只有[[dirtyAttributes|changed attribute values]]会被插入数据库
     *
     * If the table's primary key is auto-incremental and is `null` during insertion,
     * it will be populated with the actual value after insertion.
     * 如果数据表的主键是自动自增的，并且插入时为null，在插入完成后，将会填充真实的值
     *
     * For example, to insert a customer record:
     * 例如，插入一条用户记录：
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * 参数 boolean 保存记录前是否执行验证（调用验证方法）。默认是true，如果验证失败，记录就不会被保存到数据库，并且此方法会返回false
     * @param array $attributes list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性列表。默认是null，代表从数据库加载的所有属性都会被保存
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     * 返回值 boolean 属性是否合法以及记录是否插入成功
     * @throws \Exception in case insert failed.
     * 以防插入失败，抛出异常。
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }

        if (!$this->isTransactional(self::OP_INSERT)) {
            return $this->insertInternal($attributes);
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->insertInternal($attributes);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Inserts an ActiveRecord into DB without considering transaction.
     * 不考虑事务处理，把活动记录插入到数据库中
     * @param array $attributes list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性列表。默认是null，代表所有从数据库加载的所有属性都会被保存。
     * @return boolean whether the record is inserted successfully.
     * 返回值 boolean 记录是否被成功插入。
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $values)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) {
            $id = static::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->setAttribute($name, $id);
            $values[$name] = $id;
        }

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * Saves the changes to this active record into the associated database table.
     * 把活动记录的变化保存相关的数据表中
     *
     * This method performs the following steps in order:
     * 该方法按照如下的步骤执行：
     *
     * 1. call [[beforeValidate()]] when `$runValidation` is `true`. If [[beforeValidate()]]
     *    returns `false`, the rest of the steps will be skipped;
     * 1. 如果$runValidation为true，调用[[beforeValidate()]]方法。如果[[beforeValidate()]]方法返回false，会跳过剩余的步骤
     * 2. call [[afterValidate()]] when `$runValidation` is `true`. If validation
     *    failed, the rest of the steps will be skipped;
     * 2. 如果$runValidation为true，调用[[afterValidate()]]方法。如果验证失败，就跳过剩余的步骤
     * 3. call [[beforeSave()]]. If [[beforeSave()]] returns `false`,
     *    the rest of the steps will be skipped;
     * 3. 调用[[beforeSave()]]方法，如果[[beforeSave()]]方法返回false，跳过剩余步骤
     * 4. save the record into database. If this fails, it will skip the rest of the steps;
     * 4. 把记录保存到数据库。如果操作失败，跳过剩余步骤；
     * 5. call [[afterSave()]];
     * 5. 调用[[afterSave()]]方法；
     *
     * In the above step 1, 2, 3 and 5, events [[EVENT_BEFORE_VALIDATE]],
     * [[EVENT_AFTER_VALIDATE]], [[EVENT_BEFORE_UPDATE]], and [[EVENT_AFTER_UPDATE]]
     * will be raised by the corresponding methods.
     * 在上边步骤1,2,3,5中，事件[[EVENT_BEFORE_VALIDATE]],[[EVENT_AFTER_VALIDATE]], [[EVENT_BEFORE_UPDATE]], and [[EVENT_AFTER_UPDATE]]
     * 将会被相应的方法触发。
     *
     * Only the [[dirtyAttributes|changed attribute values]] will be saved into database.
     * 只有[[dirtyAttributes|changed attribute values]]数据户别保存到数据库中
     *
     * For example, to update a customer record:
     * 例如，要更新一个客户记录：
     *
     * ```php
     * $customer = Customer::findOne($id);
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->update();
     * ```
     *
     * Note that it is possible the update does not affect any row in the table.
     * 请注意，更新操作可能没有影响数据表里边的任何一行。
     * In this case, this method will return 0. For this reason, you should use the following
     * 在这种情况下，该方法返回0。由于该原因，你应该使用如下的代码去检测更新操作是否执行成功：
     * code to check if update() is successful or not:
     *
     * ```php
     * if ($customer->update() !== false) {
     *     // update successful
     *     // 更新成功
     * } else {
     *     // update failed
     *     // 更新失败
     * }
     * ```
     *
     * @param boolean $runValidation whether to perform validation (calling [[validate()]])
     * before saving the record. Defaults to `true`. If the validation fails, the record
     * will not be saved to the database and this method will return `false`.
     * 参数 boolean 保存记录之前是否执行验证。默认是true。如果验证失败，记录不会保存到数据库并且此方法返回false
     *
     * @param array $attributeNames list of attributes that need to be saved. Defaults to `null`,
     * meaning all attributes that are loaded from DB will be saved.
     * 参数 数组 需要保存的属性列表。默认是null，代表从数据库加载的所有属性都会被保存
     *
     * @return integer|false the number of rows affected, or false if validation fails
     * or [[beforeSave()]] stops the updating process.
     * 返回值 整型|false 受影响的行数，验证失败或[[beforeSave()]]方法中断了更新操作就返回false
     *
     * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
     * being updated is outdated.
     * 当乐观锁开启，并且被更新的数据过期，抛出异常。
     * @throws \Exception in case update failed.
     * 更新失败，抛出异常
     */
    public function update($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            Yii::info('Model not updated due to validation error.', __METHOD__);
            return false;
        }

        if (!$this->isTransactional(self::OP_UPDATE)) {
            return $this->updateInternal($attributeNames);
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->updateInternal($attributeNames);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Deletes the table row corresponding to this active record.
     * 删除跟该活动记录相关的数据表中的那行数据
     *
     * This method performs the following steps in order:
     * 该方法按照如下的步骤顺序执行：
     *
     * 1. call [[beforeDelete()]]. If the method returns `false`, it will skip the
     *    rest of the steps;
     * 1. 调用[[beforeDelete()]]方法。如果该方法返回false，将会跳过剩余的步骤
     * 2. delete the record from the database;
     * 2. 删除数据库中的记录
     * 3. call [[afterDelete()]].
     * 3. 调用[[afterDelete()]]方法
     *
     * In the above step 1 and 3, events named [[EVENT_BEFORE_DELETE]] and [[EVENT_AFTER_DELETE]]
     * will be raised by the corresponding methods.
     * 在上边步骤1和3中，[[EVENT_BEFORE_DELETE]] 和 [[EVENT_AFTER_DELETE]]事件将会被相应的方法触发。
     *
     * @return integer|false the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
     * 返回值 整型|false 被删除的行数，因为某些原因删除操作失败返回false。请注意，就算删除操作执行陈宫，删除的行数仍然可能是0。
     *
     * @throws StaleObjectException if [[optimisticLock|optimistic locking]] is enabled and the data
     * being deleted is outdated.
     * 抛出异常 当乐观锁开启并且被删除的数据过期。
     * @throws \Exception in case delete failed.
     * 抛出异常 当删除操作失败时
     */
    public function delete()
    {
        if (!$this->isTransactional(self::OP_DELETE)) {
            return $this->deleteInternal();
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->deleteInternal();
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Deletes an ActiveRecord without considering transaction.
     * 忽视事务，直接删除活动记录
     * @return integer|false the number of rows deleted, or `false` if the deletion is unsuccessful for some reason.
     * Note that it is possible the number of rows deleted is 0, even though the deletion execution is successful.
     * 返回值 整型|false 被删除的行数，删除失败返回false。请注意，就算删除操作执行成功，被删除的行数也有可能是0
     * @throws StaleObjectException
     * 抛出异常
     */
    protected function deleteInternal()
    {
        if (!$this->beforeDelete()) {
            return false;
        }

        // we do not check the return value of deleteAll() because it's possible
        // the record is already deleted in the database and thus the method will return 0
        // 我们没有检测deleteAll方法的返回值，因为数据库中该记录已经被删除过了，因此该方法会返回0
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $condition[$lock] = $this->$lock;
        }
        $result = static::deleteAll($condition);
        if ($lock !== null && !$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }
        $this->setOldAttributes(null);
        $this->afterDelete();

        return $result;
    }

    /**
     * Returns a value indicating whether the given active record is the same as the current one.
     * 返回表示当前活动记录和给定的活动是否一致的值。
     * The comparison is made by comparing the table names and the primary key values of the two active records.
     * 对比是通过两个活动记录的表名和主键值进行的。
     * If one of the records [[isNewRecord|is new]] they are also considered not equal.
     * 如果一个活动记录[[isNewRecord|is new]]，也会被认为它们不相等。
     * @param ActiveRecord $record record to compare to
     * 参数 需对比的活动记录
     * @return boolean whether the two active records refer to the same row in the same database table.
     * 返回值 boolean 两个活动记录是否指向相同数据表的相同行
     */
    public function equals($record)
    {
        if ($this->isNewRecord || $record->isNewRecord) {
            return false;
        }

        return static::tableName() === $record->tableName() && $this->getPrimaryKey() === $record->getPrimaryKey();
    }

    /**
     * Returns a value indicating whether the specified operation is transactional in the current [[scenario]].
     * 返回代表在当前场景下指定的操作是否是事务性处理的值。
     * @param integer $operation the operation to check. Possible values are [[OP_INSERT]], [[OP_UPDATE]] and [[OP_DELETE]].
     * 参数 整型 被检测的操作符，可能是[[OP_INSERT]], [[OP_UPDATE]] 或 [[OP_DELETE]]
     * @return boolean whether the specified operation is transactional in the current [[scenario]].
     * 返回值 boolean 当前场景下，指定的操作是否是事务性的。
     */
    public function isTransactional($operation)
    {
        $scenario = $this->getScenario();
        $transactions = $this->transactions();

        return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
    }
}
