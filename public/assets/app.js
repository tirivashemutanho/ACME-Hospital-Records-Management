document.addEventListener('DOMContentLoaded', function(){
  const typeSel = document.querySelector('select[name="type"]');
  if(!typeSel) return;
  const inpatientFields = document.querySelectorAll('.inpatient-field');
  const daycaseFields = document.querySelectorAll('.daycase-field');

  function updateFields(){
    const val = typeSel.value;
    if(val === 'outpatient'){
      inpatientFields.forEach(e=>e.style.display='none');
      daycaseFields.forEach(e=>e.style.display='none');
    } else if(val === 'inpatient'){
      inpatientFields.forEach(e=>e.style.display='block');
      daycaseFields.forEach(e=>e.style.display='none');
    } else {
      inpatientFields.forEach(e=>e.style.display='block');
      daycaseFields.forEach(e=>e.style.display='block');
    }
  }
  typeSel.addEventListener('change', updateFields);
  updateFields();
});