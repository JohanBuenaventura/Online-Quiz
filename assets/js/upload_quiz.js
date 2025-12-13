document.addEventListener('DOMContentLoaded', function(){
  function setDisabledFor(container, disabled){
    if(!container) return;
    container.querySelectorAll('input,textarea,select').forEach(function(el){ el.disabled = !!disabled; });
  }

  // ensure we have a forcing-hidden class to override page CSS if needed
  if (!document.getElementById('oq-hidden-style')) {
    var s = document.createElement('style');
    s.id = 'oq-hidden-style';
    s.innerHTML = '.oq-hidden{display:none !important; visibility:hidden !important; height:0 !important; overflow:hidden !important}';
    document.head.appendChild(s);
  }

  function updateForSelect(sel){
    var card = sel.closest('div.card') || sel.parentElement;
    var mcq = card.querySelector('.mcq-choices');
    var tf = card.querySelector('.tf-choices');
    var type = sel.value;

    // MCQ area: show/enable only for mcq
    if(mcq){
      if(type === 'mcq'){
        mcq.classList.remove('oq-hidden');
        setDisabledFor(mcq, false);
      } else {
        mcq.classList.add('oq-hidden');
        setDisabledFor(mcq, true);
      }
    }

    // TF area: show/enable only for tf
    if(tf){
      if(type === 'tf'){
        tf.classList.remove('oq-hidden');
        setDisabledFor(tf, false);
        // ensure a TF option is selected; try to map from a selected MCQ if present
        var anyChecked = tf.querySelectorAll('input[type="radio"]:checked').length > 0;
        if(!anyChecked){
          // try mapping from mcq's selected correct choice text
          var mcqChecked = card.querySelector('.mcq-choices input[type="radio"]:checked');
          if(mcqChecked){
            var radios = Array.from(card.querySelectorAll('.mcq-choices input[type="radio"]'));
            var idx = radios.indexOf(mcqChecked);
            var txtInputs = Array.from(card.querySelectorAll('.mcq-choices input[type="text"][name*="[choices]"]'));
            var txt = (txtInputs[idx] && txtInputs[idx].value) ? txtInputs[idx].value.trim().toLowerCase() : '';
            if(txt === 'true'){
              var r = card.querySelector('input[name*="[tf_correct]"][value="true"]'); if(r) r.checked = true;
            } else if(txt === 'false'){
              var r = card.querySelector('input[name*="[tf_correct]"][value="false"]'); if(r) r.checked = true;
            } else {
              var r = card.querySelector('input[name*="[tf_correct]"][value="false"]'); if(r) r.checked = true;
            }
          } else {
            var r = card.querySelector('input[name*="[tf_correct]"][value="false"]'); if(r) r.checked = true;
          }
        }
      } else {
        tf.classList.add('oq-hidden');
        setDisabledFor(tf, true);
      }
    }
  }

  document.querySelectorAll('select[name$="[type]"]').forEach(function(sel){
    updateForSelect(sel);
    sel.addEventListener('change', function(){ updateForSelect(sel); });
  });

  // validation on save
  var form = document.querySelector('form[method="post"]');
  if(form){
    form.addEventListener('submit', function(e){
      var ok = true; var msg = '';
      document.querySelectorAll('select[name$="[type]"]').forEach(function(sel){
        var type = sel.value;
        var card = sel.closest('div.card') || sel.parentElement;
        if(type === 'mcq'){
          var inputs = Array.from(card.querySelectorAll('input[type="text"][name*="[choices]"]')).filter(function(i){ return !i.disabled; });
          var any = inputs.some(function(i){ return i.value.trim() !== ''; });
          var anyCorrect = card.querySelectorAll('input[type="radio"][name*="[correct_index]"]:checked').length > 0;
          if(!any){ ok = false; msg = 'Each MCQ must have at least one choice.'; }
          else if(!anyCorrect){ ok = false; msg = 'Each MCQ must have a correct choice selected.'; }
        } else if(type === 'tf'){
          var anyTF = card.querySelectorAll('input[type="radio"][name*="[tf_correct]"]:checked').length > 0;
          if(!anyTF){ ok = false; msg = 'Select True or False for TF questions.'; }
        }
      });
      if(!ok){ e.preventDefault(); alert(msg); return false; }
    });
  }
});
