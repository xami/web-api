<?php

/**
 * This is the model class for table "crontab".
 *
 * The followings are the available columns in table 'crontab':
 * @property integer $id
 * @property integer $sid
 * @property integer $pid
 * @property integer $status
 * @property string $msg
 * @property string $error
 * @property string $time
 */
class Crontab extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Crontab the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'crontab';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('sid, pid, status, msg, error', 'required'),
			array('sid, pid, status', 'numerical', 'integerOnly'=>true),
			array('msg', 'length', 'max'=>255),
			array('error', 'length', 'max'=>1024),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, sid, pid, status, msg, error, time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'sid' => 'Sid',
			'pid' => 'Pid',
			'status' => 'Status',
			'msg' => 'Msg',
			'error' => 'Error',
			'time' => 'Time',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('sid',$this->sid);
		$criteria->compare('pid',$this->pid);
		$criteria->compare('status',$this->status);
		$criteria->compare('msg',$this->msg,true);
		$criteria->compare('error',$this->error,true);
		$criteria->compare('time',$this->time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}