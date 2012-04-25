<div class="form">
    <?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'IO',
    'enableClientValidation'=>true,
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'in'); ?>
        <?php echo $form->textArea($model,'in',array('style'=>'width:100%;height:200px;')); ?>
        <?php echo $form->error($model,'in'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'type'); ?>
        <?php echo $form->textField($model,'type'); ?>
        <?php echo $form->error($model,'type'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'out'); ?>
        <?php echo $form->textArea($model,'out',array('style'=>'width:100%;height:300px;')); ?>
        <?php echo $form->error($model,'out'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::SubmitButton('Proccese', ''); ?>
    </div>

    <?php $this->endWidget(); ?>
</div><!-- form -->