function click_row(row,type) {
	with(row.parentNode) {// �������
	//tog - ����������� ��������� ������ � ������� ��� ���������� ���������
	//sel - �������� ������ � ������� ��� ���������� ���������
	//add - ��������� ��������� �� ������ ���� ����������
	//add_tog - ����������� ��������� ������ �� ������ ���������� ���������
		for(i=1; i<=rows.length-1; i++) {
			if(rows[i]==row) {
				//alert(rows[i]);
				//if(type=='tog' || type=='add_tog') rows[i].classList.toggle('selected_row');
				//if(type=='add' || type=='sel') rows[i].classList.add('selected_row');
				if(type=='add' || type=='sel') rows[i].className='selected_row';
			}
			else {
				//alert(rows[i].classList);
				if(type!='add' && type!='add_tog') {
					//alert(rows[i].className);
					//rows[i].classList.remove('selected_row');
					rows[i].className='selectable_row';
				}
			}
}}}
