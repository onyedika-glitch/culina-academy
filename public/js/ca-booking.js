(function(){
"use strict";
var ov,mImg,mTitle,mMeta,mPrice,mDates,intlTelRetryCount=0;
function qs(s,c){return(c||document).querySelector(s);}
function note(el,msg,t){el.textContent=msg;el.className='ca-note show ca-'+t;}
function initIntlTelInputs(root){
    root=root||document;
    if(typeof window.intlTelInput!=='function'){
        setTimeout(function(){initIntlTelInputs(root);},100);
        return;
    }
    root.querySelectorAll('.ca-phone-row input[type="tel"],input[type="tel"][name="phone"],input[type="tel"][name="user_phone"]').forEach(function(input){
        if(input.name==='user_whatsapp') return;
        var hiddenWaitlist=input.closest('.ca-wlf:not(.open)');
        if(hiddenWaitlist) return;
        if(input.dataset.caIntlReady==='1' || input.dataset.intlReady==='1'){
            if(input._caIti && typeof input._caIti.setCountry==='function'){
                input._caIti.setCountry(input._caIti.getSelectedCountryData().iso2 || 'gb');
            }
            return;
        }
        input.dataset.caIntlReady='1';
        input.dataset.intlReady='1';
        var row=input.closest('.ca-phone-row');
        var form=input.closest('form');
        var hidden=row?qs('input.ca-intl-code',row):null;
        var hiddenCountry=form?qs('input[name="country"]',form):null;
        if(!hidden && form){
            hidden=qs('input[name="intl_code"]',form);
            if(hidden && hidden.type!=='hidden'){
                hidden.type='hidden';
                var field=hidden.closest('div,p,section,li');
                if(field) field.classList.add('ca-legacy-intl-code');
            }
        }
        var iti=window.intlTelInput(input,{
            initialCountry:'gb',
            separateDialCode:true,
            loadUtils:() => import('https://cdn.jsdelivr.net/npm/intl-tel-input@29.1.2/dist/js/utils.js')
        });
        input._caIti=iti;
        function syncCode(){
            var data=(typeof iti.getSelectedCountryData === "function" ? iti.getSelectedCountryData() : iti.getSelectedCountry());
            if(hidden) hidden.value=data&&data.dialCode?data.dialCode:'';
            if(hiddenCountry) hiddenCountry.value=data&&data.name?data.name:'';
        }
        input.addEventListener('countrychange',syncCode);
        input.addEventListener('input',syncCode);
        setTimeout(syncCode,300);
    });
}
function openM(){ov.classList.add('open');document.body.style.overflow='hidden';}
function closeM(){ov.classList.remove('open');document.body.style.overflow='';}
window.caClose=closeM;

function loadModal(cid){
    mDates.innerHTML='<div class="ca-load"><span class="ca-spin"></span>'+caData.i18n.loading+'</div>';
    mImg.src='';mTitle.textContent='';mMeta.textContent='';mPrice.textContent='';
    openM();
    fetch(caData.ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':caData.nonce},
        body:'action=ca_get_modal&course_id='+encodeURIComponent(cid)+'&_token='+encodeURIComponent(caData.nonce)+'&nonce='+encodeURIComponent(caData.nonce)+'&page_language='+encodeURIComponent(caData.language||'')})
    .then(function(r){return r.json();})
    .then(function(res){
        if(!res.success){mDates.innerHTML='<p style="color:#c00">'+caData.i18n.couldNotLoad+'</p>';return;}
        var d=res.data;
        mImg.src=d.image;mImg.alt=d.title;
        mTitle.textContent=d.title;mMeta.textContent=d.meta;mPrice.textContent=d.price;
        mDates.innerHTML=d.html;
        initIntlTelInputs(mDates);
    })
    .catch(function(){mDates.innerHTML='<p style="color:#c00">'+caData.i18n.networkErrorShort+'</p>';});
}

function toggleWL(btn){
    var id=btn.dataset.iid;
    var scope=btn.closest('.ca-dr,.ca-date-range-row,.ca-course-intakes,#mDates')||document;
    var el=scope.querySelector('#caWL'+id)||document.getElementById('caWL'+id);
    if(el){
        el.classList.toggle('open');
        if(el.classList.contains('open')){
            setTimeout(function(){initIntlTelInputs(el);},30);
        }
    }
}

function submitWL(btn){
    var form=btn.closest('form');
    var nb=qs('.ca-note',form);
    var fd=new FormData(form);
    fd.append('action','ca_join_waitlist');
    fd.append('_token',caData.nonce);
    fd.append('nonce',caData.nonce);
    fd.append('page_language',caData.language||document.documentElement.lang||'');
    btn.disabled=true;btn.textContent=caData.i18n.adding;
    if(nb) nb.className='ca-note';
    fetch(caData.ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':caData.nonce},body:fd})
    .then(function(r){return r.json();})
    .then(function(res){
        if(res.success){
            var msg=res.data&&res.data.message?res.data.message:caData.i18n.waitlistSuccess;
            var type=res.data&&res.data.mail_failures&&res.data.mail_failures.length?'wa':'ok';
            if(nb) note(nb,msg,type);
            form.reset();
            initIntlTelInputs(form);
        } else {
            if(nb) note(nb,caData.i18n.errorPrefix+' '+(res.data||caData.i18n.tryAgain),'er');
        }
    })
    .catch(function(){if(nb) note(nb,caData.i18n.networkError,'er');})
    .finally(function(){btn.disabled=false;btn.textContent=caData.i18n.joinWaitlist;});
}

document.addEventListener('DOMContentLoaded',function(){
    initIntlTelInputs(document);
    document.addEventListener('click',function(e){
        var t=e.target.closest('.ca-modal-trigger');
        if(t){e.preventDefault();loadModal(t.dataset.cid);return;}
        var wlToggle=e.target.closest('.ca-wlb');
        if(wlToggle){e.preventDefault();toggleWL(wlToggle);return;}
        var wlSubmit=e.target.closest('.ca-wlsub');
        if(wlSubmit){e.preventDefault();submitWL(wlSubmit);return;}
    });
    ov=document.getElementById('caOv');
    if(!ov) return;
    mImg=document.getElementById('mImg');
    mTitle=document.getElementById('mTitle');
    mMeta=document.getElementById('mMeta');
    mPrice=document.getElementById('mPrice');
    mDates=document.getElementById('mDates');
    ov.addEventListener('click',function(e){if(e.target===ov)closeM();});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')closeM();});
    // auto-open from ?ca_open=ID
    var p=new URLSearchParams(window.location.search);
    if(p.get('ca_open')) loadModal(parseInt(p.get('ca_open'),10));
});
})();