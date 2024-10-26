"use strict";Object.defineProperty(exports,Symbol.toStringTag,{value:"Module"});const e=require("vue"),_=require("vue-router"),N=require("./composable/user.cjs"),m=require("./types-D9XxK5BT.cjs"),p=require("./moment-Bp7fbP4q.cjs"),V={class:"uk-text-center uk-margin"},k=["src"],C={class:"uk-text-center uk-margin-remove"},f={key:0,class:"uk-margin-remove uk-text-meta","data-test":"meta"},x={class:"uk-description-list"},v={class:"uk-text-meta"},g={key:0,class:"uk-text-meta"},U={key:1,class:"uk-text-meta"},w={class:"uk-text-meta"},b={class:"uk-text-meta"},E={class:"uk-text-meta"},S=e.defineComponent({__name:"UserInfo",props:{user:{}},setup(i){return(o,t)=>{const n=e.resolveComponent("font-awesome-icon"),r=e.resolveComponent("UFLabel"),l=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(l,null,{default:e.withCtx(()=>[e.createElementVNode("div",V,[e.createElementVNode("img",{src:o.user.avatar,alt:"avatar",class:"uk-border-circle"},null,8,k),e.createElementVNode("h3",C,e.toDisplayString(o.user.full_name),1),o.user.user_name?(e.openBlock(),e.createElementBlock("p",f," ("+e.toDisplayString(o.user.user_name)+") ",1)):e.createCommentVNode("",!0),e.renderSlot(o.$slots,"default",{dataTest:"slot"})]),t[9]||(t[9]=e.createElementVNode("hr",null,null,-1)),e.createElementVNode("dl",x,[e.createElementVNode("dt",null,[e.createVNode(n,{icon:"envelope"}),t[0]||(t[0]=e.createTextVNode(" Email"))]),e.createElementVNode("dd",v,e.toDisplayString(o.user.email),1),e.createElementVNode("dt",null,[e.createVNode(n,{icon:"users"}),t[1]||(t[1]=e.createTextVNode(" Group"))]),o.user.group?(e.openBlock(),e.createElementBlock("dd",g,e.toDisplayString(o.user.group.name),1)):(e.openBlock(),e.createElementBlock("dd",U,t[2]||(t[2]=[e.createElementVNode("i",null,"None",-1)]))),e.createElementVNode("dt",null,[e.createVNode(n,{icon:"language"}),t[3]||(t[3]=e.createTextVNode(" Locale"))]),e.createElementVNode("dd",w,e.toDisplayString(o.user.locale_name),1),e.createElementVNode("dt",null,[e.createVNode(n,{icon:"user-shield"}),t[4]||(t[4]=e.createTextVNode(" Status"))]),e.createElementVNode("dd",b,[o.user.flag_enabled==!1?(e.openBlock(),e.createBlock(r,{key:0,severity:e.unref(m.a).Danger},{default:e.withCtx(()=>t[5]||(t[5]=[e.createTextVNode(" Disabled ")])),_:1},8,["severity"])):o.user.flag_verified==!1?(e.openBlock(),e.createBlock(r,{key:1,severity:e.unref(m.a).Warning},{default:e.withCtx(()=>t[6]||(t[6]=[e.createTextVNode(" Unactivated ")])),_:1},8,["severity"])):(e.openBlock(),e.createBlock(r,{key:2,severity:e.unref(m.a).Success},{default:e.withCtx(()=>t[7]||(t[7]=[e.createTextVNode("Active")])),_:1},8,["severity"]))]),e.createElementVNode("dt",null,[e.createVNode(n,{icon:"calendar"}),t[8]||(t[8]=e.createTextVNode(" Created on"))]),e.createElementVNode("dd",E,e.toDisplayString(e.unref(p.hooks)(o.user.created_at).format("MMMM Do, YYYY")),1)]),t[10]||(t[10]=e.createElementVNode("hr",null,null,-1)),t[11]||(t[11]=e.createElementVNode("button",{class:"uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Edit User ",-1)),t[12]||(t[12]=e.createElementVNode("button",{class:"uk-button uk-button-default uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Change User Password ",-1)),t[13]||(t[13]=e.createElementVNode("button",{class:"uk-button uk-button-default uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Disable User ",-1)),t[14]||(t[14]=e.createElementVNode("button",{class:"uk-button uk-button-danger uk-width-1-1 uk-margin-small-bottom uk-button-small"}," Delete User ",-1)),e.renderSlot(o.$slots,"default",{dataTest:"slot"})]),_:3})}}}),B=e.defineComponent({__name:"UserActivities",props:{user_name:{}},setup(i){return(o,t)=>{const n=e.resolveComponent("UFSprunjeHeader"),r=e.resolveComponent("UFSprunjeColumn"),l=e.resolveComponent("UFSprunjeTable"),a=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(a,{title:"Activities"},{default:e.withCtx(()=>[e.createVNode(l,{dataUrl:"/api/users/u/"+o.user_name+"/activities",defaultSorts:{occurred_at:"desc"}},{header:e.withCtx(()=>[e.createVNode(n,{sort:"occurred_at"},{default:e.withCtx(()=>t[0]||(t[0]=[e.createTextVNode("Activity Time")])),_:1}),e.createVNode(n,{sort:"description"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Description")])),_:1})]),body:e.withCtx(({item:s})=>[e.createVNode(r,null,{default:e.withCtx(()=>[e.createElementVNode("div",null,e.toDisplayString(e.unref(p.hooks)(s.occurred_at).format("dddd")),1),e.createElementVNode("div",null,e.toDisplayString(e.unref(p.hooks)(s.occurred_at).format("MMM Do, YYYY h:mm a")),1)]),_:2},1024),e.createVNode(r,null,{default:e.withCtx(()=>[e.createElementVNode("div",null,e.toDisplayString(s.ip_address),1),e.createElementVNode("div",null,[e.createElementVNode("i",null,e.toDisplayString(s.description),1)])]),_:2},1024)]),_:1},8,["dataUrl"])]),_:1})}}}),F={class:"uk-button uk-button-default"},y={class:"uk-button uk-button-danger uk-button-small"},h=e.defineComponent({__name:"UserRoles",props:{slug:{}},setup(i){return(o,t)=>{const n=e.resolveComponent("font-awesome-icon"),r=e.resolveComponent("UFSprunjeHeader"),l=e.resolveComponent("RouterLink"),a=e.resolveComponent("UFSprunjeColumn"),s=e.resolveComponent("UFSprunjeTable"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(u,{title:"Roles"},{default:e.withCtx(()=>[e.createVNode(s,{dataUrl:"/api/users/u/"+o.slug+"/roles",searchColumn:"name",hideFilters:""},{actions:e.withCtx(()=>[e.createElementVNode("button",F,[e.createVNode(n,{icon:"address-card"}),t[0]||(t[0]=e.createTextVNode(" Add role "))])]),header:e.withCtx(()=>[e.createVNode(r,{sort:"name"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Role")])),_:1}),e.createVNode(r,{sort:"description"},{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Description")])),_:1}),e.createVNode(r,{sort:"description"},{default:e.withCtx(()=>t[3]||(t[3]=[e.createTextVNode("Actions")])),_:1})]),body:e.withCtx(({item:d})=>[e.createVNode(a,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(l,{to:{name:"admin.role",params:{slug:d.slug}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(d.name),1)]),_:2},1032,["to"])])]),_:2},1024),e.createVNode(a,null,{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(d.description),1)]),_:2},1024),e.createVNode(a,null,{default:e.withCtx(()=>[e.createElementVNode("button",y,[e.createVNode(n,{icon:"trash"})])]),_:1})]),_:1},8,["dataUrl"])]),_:1})}}}),T=e.defineComponent({__name:"UserPermissions",props:{user_name:{}},setup(i){return(o,t)=>{const n=e.resolveComponent("UFSprunjeHeader"),r=e.resolveComponent("RouterLink"),l=e.resolveComponent("UFSprunjeColumn"),a=e.resolveComponent("UFLabel"),s=e.resolveComponent("UFSprunjeTable"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createBlock(u,{title:"Permissions"},{default:e.withCtx(()=>[e.createVNode(s,{dataUrl:"/api/users/u/"+o.user_name+"/permissions",searchColumn:"name"},{header:e.withCtx(()=>[e.createVNode(n,{sort:"name"},{default:e.withCtx(()=>t[0]||(t[0]=[e.createTextVNode("Permission")])),_:1}),e.createVNode(n,{sort:"properties"},{default:e.withCtx(()=>t[1]||(t[1]=[e.createTextVNode("Description")])),_:1}),e.createVNode(n,null,{default:e.withCtx(()=>t[2]||(t[2]=[e.createTextVNode("Has permission via roles")])),_:1})]),body:e.withCtx(({item:d})=>[e.createVNode(l,null,{default:e.withCtx(()=>[e.createElementVNode("strong",null,[e.createVNode(r,{to:{name:"admin.permission",params:{id:d.id}}},{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(d.name),1)]),_:2},1032,["to"])])]),_:2},1024),e.createVNode(l,null,{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(d.description),1)]),_:2},1024),e.createVNode(l,null,{default:e.withCtx(()=>[(e.openBlock(!0),e.createElementBlock(e.Fragment,null,e.renderList(d.roles_via,c=>(e.openBlock(),e.createBlock(r,{key:c.id,to:{name:"admin.role",params:{slug:c.slug}}},{default:e.withCtx(()=>[e.createVNode(a,null,{default:e.withCtx(()=>[e.createTextVNode(e.toDisplayString(c.name),1)]),_:2},1024)]),_:2},1032,["to"]))),128))]),_:2},1024)]),_:1},8,["dataUrl"])]),_:1})}}}),D={"uk-grid":""},j={class:"uk-width-1-3"},$={class:"uk-width-2-3"},A={class:"uk-child-width-1-1","uk-grid":""},L=e.defineComponent({__name:"UserView",setup(i){const o=_.useRoute(),{user:t,error:n}=N.useUserAdminApi(o);return(r,l)=>{const a=e.resolveComponent("UFHeaderPage"),s=e.resolveComponent("UFAlert"),u=e.resolveComponent("UFCardBox");return e.openBlock(),e.createElementBlock(e.Fragment,null,[e.createVNode(a,{title:"User details",caption:"User information page"}),e.unref(n)?(e.openBlock(),e.createBlock(u,{key:0},{default:e.withCtx(()=>[e.createVNode(s,{alert:e.unref(n)},null,8,["alert"])]),_:1})):(e.openBlock(),e.createElementBlock(e.Fragment,{key:1},[e.createElementVNode("div",D,[e.createElementVNode("div",j,[e.createVNode(S,{user:e.unref(t)},null,8,["user"])]),e.createElementVNode("div",$,[e.createVNode(h,{slug:e.unref(t).user_name},null,8,["slug"])])]),e.createElementVNode("div",A,[e.createElementVNode("div",null,[e.createVNode(T,{user_name:e.unref(t).user_name},null,8,["user_name"])]),e.createElementVNode("div",null,[e.createVNode(B,{user_name:r.$route.params.user_name.toString()},null,8,["user_name"])])])],64))],64)}}});exports.default=L;
