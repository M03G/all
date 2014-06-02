<div style="margin-top:-50px;">
	<table class="table table-striped table-bordered">
		<tr>
			<td></td>
			<? foreach ($tpl['area'] as $key_area => $area) {?>
			<th><?= $area['nameu']?></th>
			<?}?>
			<td></td>
		</tr>
		<?foreach ($tpl['overview']['bort'] as $key_bort => $bort) {?>
		<tr>
			<td><?= $bort['numb']?></td>
				<?$i = 0?>
				<?foreach ($bort['templates'] as $key_template => $template) {?>
					<?if ($template['area'] != $i){?>
						<?if ($i === 0){?>
						<td>
						<?}else{?>
						</td><td>
						<?}?>
					<?}?>
					<span class="progress" style="margin:0;width:95px;display:inline-block;height:40px;<?= (!isset($template['complete']))? 'background-color:#d2d2d2':'background-color:#999999'?>" title="<?= $template['title']?>">
						<?if($template['complete']){?>
							<div class="progress-bar progress-bar-success" style="width: <?= $template['complete']?>%;" title="<?= $template['title']?>. Выполнено на <?= $template['complete']?>%"></div>  
						<?}?>
					</span>
					<?$i = $template['area']?>
				<?}?>
				</td>
				<td>%</td>
			</tr>
		<?}?>
	</table>
	<?print_ra($tpl, false, '600px')?>
</div>
