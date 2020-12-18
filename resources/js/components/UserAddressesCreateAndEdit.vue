<template>
  <div>
    <div class="row">
      <div class="col-md-10 offset-lg-1">
        <div class="card">
          <!-- 输出后端报错开始 -->
          <select-districts @address="addressData"> </select-districts>
          <form class="form-horizontal" role="form" method="post">
            <!-- <input type="hidden" name="province" value="{{csrf-token}}" /> -->
            <input type="hidden" name="province" v-model="province" />
            <input type="hidden" name="city" v-model="city" />
            <input type="hidden" name="district" v-model="district" />
            <div class="form-group row">
              <label class="col-form-label text-md-right col-sm-2"
                >详细地址</label
              >
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  name="address"
                  value=""
                  v-model="address"
                />
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-md-right col-sm-2">邮编*</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  name="zip"
                  value=""
                  v-model="zip"
                />
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-md-right col-sm-2">姓名</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  name="address"
                  v-model="contact_name"
                />
              </div>
            </div>
            <div class="form-group row">
              <label class="col-form-label text-md-right col-sm-2">电话</label>
              <div class="col-sm-9">
                <input
                  type="text"
                  class="form-control"
                  name="contact_phone"
                  v-model="contact_phone"
                />
              </div>
            </div>
            <div class="form-group row text-center">
              <div class="col-12">
                <button
                  type="button"
                  class="btn btn-primary"
                  @click="submitAddressData"
                >
                  提交
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SelectDistricts from "./SelectDistricts";
import { Hub } from "./../event-bus";
import Vue from "vue";
import { Toast } from "vant";

Vue.use(Toast);
export default {
  name: "",
  components: {
    SelectDistricts,
  },
  data() {
    return {
      province: "", // 省
      city: "", // 市
      district: "", // 区
      address: "", // 详细地址
      contact_name: "", //收货人姓名
      zip: "", //收货人邮编
      contact_phone: "", //收货人电话
    };
  },
  created() {
  },
  mounted() {
    this.addressData();
  },
  methods: {
    // 把参数 val 中的值保存到组件的数据中
    onDistrictChanged(val) {
      if (val.length === 3) {
        this.province = val[0];
        this.city = val[1];
        this.district = val[2];
      }
    },
    //子组件传过来的值
    addressData() {
      Hub.$on("provinceId", (data) => {
        this.province = data.name;
      });
      Hub.$on("city", (data) => {
        this.city = data.name;
      });
      Hub.$on("district", (data) => {
        this.district = data.name;
      });
    },
    submitAddressData() {
      if (!this.province) {
        Toast("请选择省");
        return;
      }
      if (!this.city) {
        Toast("请选择市");
        return;
      }
      if (!this.district) {
        Toast("请选择区");
        return;
      }
      if (this.address.match(/^\s*$/)) {
        Toast("请填详细地址");
        return;
      }
      if (!this.contact_name) {
        Toast("请填姓名");
        return;
      }
      if (!this.contact_phone) {
        Toast("请填电话");
        return;
      }
      //   console.log(this.zip.match(/^[1-9]\\d{5}$/))
      //   if (!this.zip.match(/^[1-9]\\d{5}$/)) {
      //     Toast("请输入6位数字邮编");
      //     return;
      //   }
      //   if (!this.contact_phone.match(/^[1-9]\\d{10}$/)) {
      //     Toast("请输入正确的手机号");
      //     return;
      //   }

      //提交数据
      axios
        .post("/user_addresses/store", {
          province: this.province,
          city: this.city,
          district: this.district,
          address: this.address,
          contact_name: this.contact_name,
          contact_phone: this.contact_phone,
          zip: this.zip,
        })
        .then((res) => {
          if (res.data.code === 200) {
              console.log(res.data)
            Toast(res.data.data);
            location.href=res.data.route
          }
        });
    },
  },
};
</script>
<style scoped>
</style>
