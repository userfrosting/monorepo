"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const e=require("vue"),n=require("../axios-tuVKNgv9.cjs"),o=require("../types-D9XxK5BT.cjs");function i(s){const a=e.ref(!1),t=e.ref(),u=e.ref({id:0,slug:"",name:"",description:"",created_at:"",updated_at:"",deleted_at:null,users_count:0});async function l(){a.value=!0,t.value=null,await n.axios.get("/api/roles/r/"+s.params.slug).then(r=>{u.value=r.data}).catch(r=>{t.value={description:"An error as occurred",style:o.a.Danger,...r.response.data}}).finally(()=>{a.value=!1})}return e.watch(()=>s.params.slug,()=>{l()},{immediate:!0}),{role:u,error:t,loading:a}}exports.useRoleApi=i;
