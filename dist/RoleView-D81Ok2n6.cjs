"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const e=require("vue"),i=require("vue-router"),m=require("./composable/role.cjs"),p={class:"uk-text-center"},_={class:"uk-text-center uk-margin-remove"},V={class:"uk-text-meta"},N={class:"uk-description-list"},C={class:"uk-badge"},k=e.defineComponent({__name:"RoleInfo",props:{role:{}},setup(c){return(n,t)=>{const o=e.resolveComponent("font-awesome-icon"),r=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(r,null,{default:e.withCtx(()=>[e.createElementVNode("div",p,[e.createVNode(o,{icon:"address-card",class:"fa-5x"})]),e.createElementVNode("h3",_,e.toDisplayString(n.role.name),1),e.createElementVNode("p",V,e.toDisplayString(n.role.description),1),t[1]||(t[1]=e.createElementVNode("hr",null,null,-1)),e.createElementVNode("dl",N,[e.createElementVNode("dt",null,[e.createVNode(o,{icon:"users"}),t[0]||(t[0]=e.createTextVNode(" Users"))]),e.createElementVNode("dd",null,[e.createElementVNode("span",C,e.toDisplayString(n.role.users_count),1)])]),t[2]||(t[2]=e.createElementVNode("hr",null,null,-1)),t[3]||(t[3]=e.createElementVNode("button",{class:"uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Edit Role ",-1)),t[4]||(t[4]=e.createElementVNode("button",{class:"uk-button uk-button-danger uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Delete Role ",-1)),e.renderSlot(n.$slots,"default",{dataTest:"slot"})]),_:3})}}}),f={class:"uk-button uk-button-default"},x={class:"uk-text-meta"},b={class:"uk-button uk-button-danger uk-button-small"},w=e.defineComponent({__name:"RoleUsers",props:{slug:{}},setup(c){return(n,t)=>{const o=e.resolveComponent("font-awesome-icon"),r=e.resolveComponent("UFSprunjeHeader"),d=e.resolveComponent("RouterLink"),l=e.resolveComponent("UFSprunjeColumn"),a=e.resolveComponent("UFSprunjeTable"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(u,{title:"Role Users"},{default:e.withCtx(()=>[e.createVNode(a,{dataUrl:"/api/roles/r/"+n.slug+"/users",searchColumn:"name",hideFilters:""},{actions:e.withCtx(()=>[e.createElementVNode("button",f,[e.createVNode(o,{icon:"user-plus"}),t[0]||(t[0]=e.createTextVNode(" Add user "))])]),header:e.withCtx(()=>[e.createVNode(r,{sort:"name"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("User")])),_:1}),e.createVNode(r,null,{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Actions")])),_:1})]),body:e.withCtx(({item:s})=>[e.createVNode(l,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(d,{to:{name:"admin.user",params:{user_name:s.user_name}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(s.full_name)+" ("+e.toDisplayString(s.user_name)+") ",1)]),_:2},1032,["to"])]),e.createElementVNode("div",x,e.toDisplayString(s.email),1)]),_:2},1024),e.createVNode(l,null,{default:e.withCtx(()=>[e.createElementVNode("button",b,[e.createVNode(o,{icon:"trash"})])]),_:1})]),_:1},8,["dataUrl"])]),_:1})}}}),g={class:"uk-button uk-button-default"},h={class:"uk-button uk-button-danger uk-button-small"},U=e.defineComponent({__name:"RolePermissions",props:{role:{}},setup(c){return(n,t)=>{const o=e.resolveComponent("font-awesome-icon"),r=e.resolveComponent("UFSprunjeHeader"),d=e.resolveComponent("RouterLink"),l=e.resolveComponent("UFSprunjeColumn"),a=e.resolveComponent("UFSprunjeTable"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(u,{title:"Permissions"},{default:e.withCtx(()=>[e.createVNode(a,{dataUrl:"/api/roles/r/"+n.role+"/permissions",searchColumn:"name"},{actions:e.withCtx(()=>[e.createElementVNode("button",g,[e.createVNode(o,{icon:"key"}),t[0]||(t[0]=e.createTextVNode(" Add permission "))])]),header:e.withCtx(()=>[e.createVNode(r,{sort:"name"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Permission")])),_:1}),e.createVNode(r,{sort:"properties"},{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Description")])),_:1}),e.createVNode(r,null,{default:e.withCtx(()=>t[3]||(t[3]=[e.createTextVNode("Actions")])),_:1})]),body:e.withCtx(({item:s})=>[e.createVNode(l,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(d,{to:{name:"admin.permission",params:{id:s.id}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(s.name),1)]),_:2},1032,["to"])])]),_:2},1024),e.createVNode(l,null,{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(s.description),1)]),_:2},1024),e.createVNode(l,null,{default:e.withCtx(()=>[e.createElementVNode("button",h,[e.createVNode(o,{icon:"trash"})])]),_:1})]),_:1},8,["dataUrl"])]),_:1})}}}),v={"uk-grid":""},F={class:"uk-width-1-3"},E={class:"uk-width-2-3"},S={class:"uk-child-width-1-1","uk-grid":""},B=e.defineComponent({__name:"RoleView",setup(c){const n=i.useRoute(),{role:t,error:o}=m.useRoleApi(n);return(r,d)=>{const l=e.resolveComponent("UFHeaderPage"),a=e.resolveComponent("UFAlert"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createElementBlock(e.Fragment,null,[e.createVNode(l,{title:"Role details",caption:"Role information page"}),e.unref(o)?(e.openBlock(),e.createBlock(u,{key:0},{default:e.withCtx(()=>[e.createVNode(a,{alert:e.unref(o)},null,8,["alert"])]),_:1})):(e.openBlock(),e.createElementBlock(e.Fragment,{key:1},[e.createElementVNode("div",v,[e.createElementVNode("div",F,[e.createVNode(k,{role:e.unref(t)},null,8,["role"])]),e.createElementVNode("div",E,[e.createVNode(w,{slug:e.unref(t).slug},null,8,["slug"])])]),e.createElementVNode("div",S,[e.createElementVNode("div",null,[e.createVNode(U,{role:r.$route.params.slug.toString()},null,8,["role"])])])],64))],64)}}});exports.default=B;
