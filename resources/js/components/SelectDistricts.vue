<template>
  <div>
    <div class="card-header">
      <h2 class="text-center">{{ isUpdate ? "修改" : "新增" }}收货地址</h2>
    </div>
    <div class="card-body">
      <form class="form-horizontal" role="form">
        <!-- inline-template 代表通过内联方式引入组件 -->
        <div class="form-row">
          <label class="col-form-label col-sm-2 text-md-right">省市区</label>
          <div class="col-sm-3">
            <select class="form-control" v-model="provinceId">
              <option value="">选择省</option>
              <option v-for="(name, id) in provinces" :key="id" :value="id">
                {{ name }}
              </option>
            </select>
          </div>
          <div class="col-sm-3">
            <select class="form-control" v-model="cityId">
              <option value="">选择市</option>
              <option v-for="(name, id) in cities" :key="id" :value="id">
                {{ name }}
              </option>
            </select>
          </div>
          <div class="col-sm-3">
            <select class="form-control" v-model="districtId">
              <option value="">选择区</option>
              <option v-for="(name, id) in districts" :key="id" :value="id">
                {{ name }}
              </option>
            </select>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
// 从刚刚安装的库中加载数据
const addressData = require("china-area-data/v5/data");
// 引入 lodash，lodash 是一个实用工具库，提供了很多常用的方法
import _ from "lodash";
import { Hub } from "./../event-bus";
export default {
  name: "",
  props: {
    childAddress: {
      type: Object,
    },
  },
  data() {
    return {
      provinces: addressData["86"], //省列表
      cities: {}, //市列表
      districts: "", //地区列表
      provinceId: "", //当前选中省
      cityId: "", //当前权重的市
      districtId: "", //当前选中的区
      isUpdate: false,
    };
  },
  created() {
    //填充赏已选项，先判断如果是修改在执行循环
    if (this.childAddress.province) {
      this.isUpdate = true;
      document.title = "修改收货地址";
      this.getAddressData(
        addressData["86"],
        this.childAddress.province,
        "provinceId"
      );
      this.getAddressData(
        addressData[this.provinceId],
        this.childAddress.city,
        "city"
      );
      this.getAddressData(
        addressData[this.cityId],
        this.childAddress.district,
        "district"
      );
    }
  },
  //定义观察器
  watch: {
    //当选择省发生变化时间触发
    provinceId(newId) {
      //当没有选中时，市和区清空
      if (!newId) {
        this.cities = {};
        this.cityId = "";
        return;
      }
      this.cities = addressData[newId];
      let selectedProvinceData = this.setData(
        "province",
        "86",
        this.provinceId
      );
      Hub.$emit("provinceId", selectedProvinceData);
    },

    //当选择市发生变化触发 provinceId
    cityId(newId) {
      if (!newId) {
        this.districts = "";
        this.districtId = "";
        return;
      }
      this.districts = addressData[newId];

      let selectedProvinceData = this.setData(
        "city",
        this.provinceId,
        this.cityId
      );
      Hub.$emit("city", selectedProvinceData);
    },
    districtId(newId) {
      if (newId) {
        let selectedProvinceData = this.setData(
          "district",
          this.cityId,
          this.districtId
        );
        Hub.$emit("district", selectedProvinceData);
      }
    },
  },
  methods: {
    //获取省市区 ID
    getAddressData(allAddress, addressName, name) {
      let a = 1;
      for (let key in allAddress) {
        if (allAddress[key] === addressName) {
          if (name === "provinceId") {
            this.provinceId = key;
            break;
          }
          if (name === "city") {
            this.cityId = key;
            break;
          }
          if (name === "district") {
            this.districtId = key;
            break;
          }
        }
        console.log(a++);
      }
    },
    setData(name, val1, id) {
      return { name: addressData[val1][id], id: id };
    },
  },
};
</script>
<style scoped>
</style>
